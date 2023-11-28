<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisioner;

use CommentStoreComment;
use Language;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MWContentSerializationException;
use MWException;
use MWStake\MediaWiki\Component\ContentProvisioner\EntityKey;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisioner;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage;
use MWStake\MediaWiki\Component\ContentProvisioner\Output\NullOutput;
use MWStake\MediaWiki\Component\ContentProvisioner\OutputAwareInterface;
use MWStake\MediaWiki\Component\ContentProvisioner\OutputInterface;
use MWStake\MediaWiki\Component\ContentProvisioner\UpdateLogStorageTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Status;
use TextContent;
use Title;
use TitleFactory;
use User;

class DefaultContentProvisioner implements
	LoggerAwareInterface,
	OutputAwareInterface,
	IContentProvisioner
{
	use UpdateLogStorageTrait;

	/**
	 * Logger object
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * Manifest list provider
	 *
	 * @var IManifestListProvider
	 */
	private $manifestListProvider = null;

	/**
	 * Wiki content language
	 *
	 * @var Language
	 */
	private $wikiLang;

	/**
	 * Language fallback service.
	 * Used to get fallback language for cases when ContentProvisioner does not support
	 * wiki content language. In such cases we need to find the most suitable "fallback" language.
	 *
	 * @var LanguageFallback
	 */
	private $languageFallback;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * Helps to recognize manifests which should be processed by that provisioner.
	 * Used in {@link IManifestListProvider}.
	 *
	 * @var string
	 */
	private $manifestsKey;

	/**
	 * WikiPage factory service
	 *
	 * @var WikiPageFactory
	 */
	private $wikiPageFactory;

	/**
	 * @param Language $wikiLang Wiki content language
	 * @param LanguageFallback $languageFallback Language fallback service
	 * @param TitleFactory $titleFactory Title factory service
	 * @param WikiPageFactory $wikiPageFactory WikiPage factory service
	 * @param string $manifestsKey Manifests key
	 */
	public function __construct(
		Language $wikiLang,
		LanguageFallback $languageFallback,
		TitleFactory $titleFactory,
		WikiPageFactory $wikiPageFactory,
		string $manifestsKey
	) {
		$this->logger = new NullLogger();
		$this->output = new NullOutput();

		$this->maintenanceUser = User::newSystemUser( 'MediaWiki default' );

		$this->manifestsKey = $manifestsKey;
		$this->wikiLang = $wikiLang;
		$this->languageFallback = $languageFallback;
		$this->titleFactory = $titleFactory;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output ): void {
		$this->output = $output;
	}

	/**
	 * @inheritDoc
	 */
	public function setManifestListProvider( IManifestListProvider $manifestListProvider ): void {
		$this->manifestListProvider = $manifestListProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function provision(): Status {
		if ( $this->manifestListProvider === null ) {
			$this->logger->error( 'No manifest list provider set!' );

			return Status::newFatal( 'Import failed to begin. See logs for details.' );
		}

		$manifestsList = $this->manifestListProvider->provideManifests( $this->manifestsKey );

		$this->output->write( "...ContentProvisioner: import started...\n" );

		if ( $manifestsList ) {
			foreach ( $manifestsList as $absoluteManifestPath ) {
				if ( file_exists( $absoluteManifestPath ) ) {
					$this->output->write( "...Processing manifest file: '$absoluteManifestPath' ...\n" );
					$this->processManifestFile( $absoluteManifestPath );
				} else {
					$this->output->write( "...Manifest file does not exist: '$absoluteManifestPath'\n" );
				}
			}
		} else {
			$this->output->write( "No manifests to import...\n" );
		}

		return Status::newGood();
	}

	/**
	 * Gets list of pages to import from manifest file.
	 * Only pages with suitable language will be imported, others will be skipped.
	 *
	 * For every page at first we check if it already exists.
	 * If it exists - compare its SHA1 hash with SHA1 saved in manifest.
	 *
	 * * If page's SHA1 equals to SHA1 saved in manifest - then page is already up-to-date.
	 * 		Nothing to do here.
	 * * If page's SHA1 equals to any of previous SHA1 saved in manifest - then page is outdated.
	 * Update it with fresh content. Path to page content is got from manifest file.
	 * * If page's SHA1 differs from any of saved in manifest SHA1 - then page was changed by user.
	 * Don't touch it in such case.
	 *
	 * @param string $manifestPath
	 * @return void
	 * @throws MWException
	 * @throws MWContentSerializationException
	 */
	private function processManifestFile( string $manifestPath ): void {
		$pagesList = json_decode( file_get_contents( $manifestPath ), true );

		$availableLanguages = [];
		foreach ( $pagesList as $pageTitle => $pageData ) {
			$availableLanguages[$pageData['lang']] = true;
		}

		$importLanguage = new ImportLanguage( $this->languageFallback, $this->wikiLang->getCode() );
		$importLanguageCode = $importLanguage->getImportLanguage(
			array_keys( $availableLanguages )
		);
		$this->output->write( "...Language to import content: $importLanguageCode\n" );

		foreach ( $pagesList as $pageTitle => $pageData ) {
			$this->output->write( "... Processing page: $pageTitle\n" );

			if ( $pageData['lang'] !== $importLanguageCode ) {
				$this->output->write( "... Wrong page language. Skipping...\n" );
				continue;
			}

			if ( !isset( $pageData['sha1'] ) || !isset( $pageData['content_path'] ) ) {
				$this->output->write( "Wikitext content is not available!\n" );
				continue;
			}

			$targetTitle = $pageData['target_title'];
			$title = $this->titleFactory->newFromText( $targetTitle, NS_MAIN );
			$pageContentPath = dirname( $manifestPath ) . $pageData['content_path'];

			$prefixedDbKey = $title->getPrefixedDBkey();

			if ( !$title->exists( Title::READ_LATEST ) ) {
				$this->output->write( "...Creating page '$prefixedDbKey'...\n" );

				$entityKey = new EntityKey( 'DefaultContentProvisioner', $prefixedDbKey );

				// This title was already imported, but does not exist now.
				// It should have been removed by user, so no need to import it again
				if ( $this->entityWasSynced( $entityKey ) ) {
					$this->output->write( "Wiki page was synced, but at some point removed by user. Skipping...\n" );
					continue;
				}

				$this->importWikiContent( $title, $pageContentPath );

				// Save entry in database
				$this->upsertEntitySyncRecord( $entityKey );
			} else {
				$currentHash = $this->getContentHash( $title );

				// If hashes are equal - then this page is exactly in the same state in which it was delivered
				if ( $currentHash === $pageData['sha1'] ) {
					// Currently nothing to do here
					$this->output->write( "Wiki page already exists, nothing to update here.\n" );
				} else {
					// If hashes differ - then this page either has old content or was touched by user.
					// So we'll check if current content hash equals one of the old hashes of page content.
					// If current hash equals one of the old ones - then page just has old content.
					// So we can safely update its content.

					// In other case page probably was touched by user, so we should do nothing without prompt.
					$changedByUser = true;

					$oldHashes = $pageData['old_sha1'];
					foreach ( $oldHashes as $hash ) {
						if ( $currentHash === $hash ) {
							$changedByUser = false;
							break;
						}
					}

					if ( !$changedByUser ) {
						// Page content is just outdated, so update it
						$this->output->write( "Wiki page already exists, but it has outdated content.\n" );
						$this->output->write( "...Updating page '$prefixedDbKey'...\n" );

						$this->importWikiContent( $title, $pageContentPath );

						$entityKey = new EntityKey( 'DefaultContentProvisioner', $prefixedDbKey );

						// Update entry in database
						$this->upsertEntitySyncRecord( $entityKey );
					} else {
						// User did some changes to the page, do nothing for now
						$this->output->write( "Wiki page already exists, but it was changed by user! Skipping...\n" );
					}
				}
			}
		}
	}

	/**
	 * Imports specified wiki page into the wiki
	 *
	 * @param Title $title Target title, which should be imported
	 * @param string $contentPath Path to the page content. Usually retrieved from manifest file
	 * @return bool <tt>true</tt> if success, <tt>false</tt> otherwise
	 * @throws MWException
	 * @throws MWContentSerializationException
	 */
	private function importWikiContent( Title $title, string $contentPath ): bool {
		$pageContent = file_get_contents( $contentPath );
		if ( !$pageContent ) {
			$this->logger->error( "Page '{$title->getDBkey()}': failed to retrieve page content!" );
			return false;
		}

		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$content = $wikiPage->getContentHandler()->makeContent( $pageContent, $title );

		$comment = CommentStoreComment::newUnsavedComment( 'Autogenerated' );

		$updater = $wikiPage->newPageUpdater( $this->maintenanceUser );
		$updater->setContent( SlotRecord::MAIN, $content );
		$newRevision = $updater->saveRevision( $comment );
		if ( $newRevision instanceof RevisionRecord ) {
			return true;
		} else {
			$this->logger->error( "Page '{$title->getDBkey()}': failed to create page!" );
			return false;
		}
	}

	/**
	 * Gets SHA1-hash of the latest revision content of specified title
	 *
	 * @param Title $title Processing title
	 * @return string SHA1-hash of page's the latest revision content,
	 * 		or empty string if content was not recognized
	 */
	private function getContentHash( Title $title ): string {
		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );

		$updater = $wikiPage->newPageUpdater( $this->maintenanceUser );

		$parentRevision = $updater->grabParentRevision();
		if ( $parentRevision === null ) {
			return '';
		}

		$content = $parentRevision->getContent( SlotRecord::MAIN );
		if ( $content instanceof TextContent ) {
			$text = $content->getText();

			return sha1( $text );
		}

		return '';
	}
}
