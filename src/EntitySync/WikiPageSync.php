<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\EntitySync;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use MWContentSerializationException;
use MWStake\MediaWiki\Component\ContentProvisioner\EntitySync;
use MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider;
use MWUnknownContentModelException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WikiPageSync extends EntitySync implements LoggerAwareInterface {

	/**
	 * Map where key is wiki page prefixed text, and value is data array.
	 * Data array contains "content_path" and "target_title" entries.
	 *
	 * @var array
	 */
	private $wikiPages = [];

	/**
	 * If wiki pages already were collected or not.
	 *
	 * @var bool
	 */
	private $wikiPagesCollected = false;

	/**
	 * User which is used to edit pages content
	 *
	 * @var User
	 */
	private $maintenanceUser;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var WikiPageFactory
	 */
	private $wikiPageFactory;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @var Language
	 */
	private $wikiLang;

	/**
	 * @var LanguageFallback
	 */
	private $languageFallback;

	/**
	 * @param TitleFactory $titleFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param Language $wikiLang
	 * @param LanguageFallback $languageFallback
	 */
	public function __construct(
		TitleFactory $titleFactory,
		WikiPageFactory $wikiPageFactory,
		Language $wikiLang,
		LanguageFallback $languageFallback
	) {
		$this->logger = new NullLogger();
		$this->maintenanceUser = User::newSystemUser( 'MediaWiki default' );

		$this->titleFactory = $titleFactory;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->wikiLang = $wikiLang;
		$this->languageFallback = $languageFallback;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	protected function getProvisionerKey(): string {
		return 'DefaultContentProvisioner';
	}

	/**
	 * @inheritDoc
	 */
	protected function doSync( string $dbPrefixedKey ): Status {
		$status = Status::newGood();

		if ( !$this->wikiPagesCollected ) {
			$this->collectWikiPages();
		}

		if ( !$this->wikiPages[$dbPrefixedKey] ) {
			return $status->error( 'No content was found in any of manifests' );
		}

		$title = $this->titleFactory->newFromText( $dbPrefixedKey );
		$pageContentPath = $this->wikiPages[$dbPrefixedKey]['contentPath'];

		try {
			if ( !$this->importWikiContent( $title, $pageContentPath ) ) {
				$status->error( 'Import failed' );
			}
		} catch ( MWContentSerializationException | MWUnknownContentModelException $e ) {
			$this->logger->error( $e->getMessage() );
		}

		return $status;
	}

	/**
	 * @return void
	 */
	private function collectWikiPages() {
		$enabledExtensions = array_keys( ExtensionRegistry::getInstance()->getAllThings() );

		$manifestListProvider = new StaticManifestProvider( $enabledExtensions, $GLOBALS['IP'] );

		$wikiPageManifests = $manifestListProvider->provideManifests( 'DefaultContentProvisioner' );

		$pages = [];
		foreach ( $wikiPageManifests as $absoluteManifestPath ) {
			$pagesList = json_decode( file_get_contents( $absoluteManifestPath ), true );

			$availableLanguages = [];
			foreach ( $pagesList as $titleKey => $pageData ) {
				$availableLanguages[$pageData['lang']] = true;
			}

			$importLanguage = new ImportLanguage( $this->languageFallback, $this->wikiLang->getCode() );
			$importLanguageCode = $importLanguage->getImportLanguage(
				array_keys( $availableLanguages )
			);

			foreach ( $pagesList as $titleKey => $pageData ) {
				if ( $pageData['lang'] !== $importLanguageCode ) {
					continue;
				}

				$prefixedDbKey = $pageData['target_title'];

				$pages[$prefixedDbKey]['contentPath'] = dirname( $absoluteManifestPath )
					. $pageData['content_path'];
			}
		}

		$this->wikiPages = $pages;
		$this->wikiPagesCollected = true;
	}

	/**
	 * Imports specified wiki page into the wiki
	 *
	 * @param Title $title Target title, which should be imported
	 * @param string $contentPath Path to the page content. Usually retrieved from manifest file
	 * @return bool <tt>true</tt> if success, <tt>false</tt> otherwise
	 * @throws MWContentSerializationException
	 * @throws MWUnknownContentModelException
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
}
