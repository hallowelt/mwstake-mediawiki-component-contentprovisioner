<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Tests;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;
use TextContent;
use Title;
use WikiPage;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner
 * @group Database
 */
class ContentProvisionerTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @inheritDoc
	 */
	public function addDBDataOnce() {
		// For case when wiki page already exists, but it's just outdated
		$this->insertPage( 'Test_page_4', 'Outdated page content' );
		$this->insertPage( 'Test_template_3', 'Outdated template content', NS_TEMPLATE );

		// For case when wiki page already exists, but it was created/changed by user
		$this->insertPage( 'Test_page_5', 'Regular page content, added by user' );
		$this->insertPage( 'Test_template_4', 'Template page content, added by user', NS_TEMPLATE );
	}

	/**
	 * Covers case with regular import of English wiki pages.
	 * No special cases here.
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner::provision()
	 */
	public function testRegularEnglish() {
		$manifestList = [
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent1/Content/Default/manifest.json',
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent2/Content/manifest.json',
		];

		$importLangCode = 'en';

		$this->performTest( $manifestList, $importLangCode );
	}

	/**
	 * Covers case with regular import of German wiki pages.
	 * No special cases here.
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner::provision()
	 */
	public function testRegularGerman() {
		$manifestList = [
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent1/Content/Default/manifest.json',
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent2/Content/manifest.json',
		];

		$importLangCode = 'de';

		$this->performTest( $manifestList, $importLangCode );
	}

	/**
	 * Covers case when wiki page which should be imported already exists.
	 *
	 * "sha1" hash of page content matches one of old "sha1" hashes, saved in manifest.
	 * It means that this page is just outdated, so its newer version will be imported.
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner::provision()
	 */
	public function testPageOutdated() {
		$manifestList = [
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent3/Content/manifest.json'
		];

		$importLangCode = 'en';

		$this->performTest( $manifestList, $importLangCode );
	}

	/**
	 * Covers case when wiki page which should be imported already exists.
	 *
	 * "sha1" hash of page content DOES NOT match any of old "sha1" hashes, saved in manifest.
	 * It means that this page was created/changed by user.
	 * Content provisioner will do nothing in that case.
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\DefaultContentProvisioner::provision()
	 */
	public function testPageChangedByUser() {
		$manifestList = [
			__DIR__ . '/data/wiki_root/extensions/ExtensionWithContent4/Content/manifest.json'
		];

		$importLangCode = 'en';

		// We do not need to check imported pages inside of this method
		// Because this method checks if pages were correctly imported
		// Actually, we need to make sure that pages WERE NOT imported
		// That would be expected behavior in current case
		// So checks will be done manually afterwards
		$this->performTest( $manifestList, $importLangCode, false );

		// Check that pages added/changed by user were not touched
		$expectedPages = [
			'Test_page_5' => 'Regular page content, added by user',
			'Template:Test_template_4' => 'Template page content, added by user'
		];

		foreach ( $expectedPages as $pageTitle => $expectedContent ) {
			$title = Title::newFromText( $pageTitle );

			$actualContent = $this->getPageContent( $title );

			$this->assertEquals(
				$expectedContent,
				$actualContent,
				'Page, added by user, should not be changed!'
			);
		}
	}

	/**
	 * Performs an actual test.
	 * Imports wiki pages on specified language from specified manifests.
	 * Then checks if all pages, which should be imported, are actually imported.
	 *
	 * @param array $manifestList List with paths of manifests, which are used for import
	 * @param string $importLangCode Code of language, on which import is done.
	 * 		Pages on languages different from specified - should not be imported.
	 * @param boolean $check If we need to check imported pages inside of this method.
	 * 		Pass <tt>false</tt> if some custom or more complex checks should be done,
	 * 		outside of this method.
	 *		In such case content provisioner will just do regular import, no pages checks will be done.
	 * 		Example: {@link ContentProvisionerTest::testPageChangedByUser()}
	 */
	private function performTest(
		array $manifestList,
		string $importLangCode,
		bool $check = true
	): void {
		$manifestListProviderMock = $this->createMock( IManifestListProvider::class );
		$manifestListProviderMock->method( 'provideManifests' )->willReturn( $manifestList );

		$rootPath = __DIR__ . '/data/wiki_root';

		$services = MediaWikiServices::getInstance();

		$wikiLang = $services->getLanguageFactory()->getLanguage( $importLangCode );
		$fallbackLanguage = $services->getLanguageFallback();
		$titleFactory = $services->getTitleFactory();

		$contentProvisioner = new DefaultContentProvisioner(
			$wikiLang,
			$fallbackLanguage,
			$titleFactory,
			'ManifestsKey'
		);
		$contentProvisioner->setManifestListProvider( $manifestListProviderMock );
		$contentProvisioner->provision();

		// Check that all wiki pages were successfully imported
		if ( $check ) {
			// Collect all wiki pages which should be imported, and "sha1" hash of their content
			$expectedWikiPages = $this->getExpectedWikiPages( $manifestList, $rootPath, $importLangCode );

			$this->checkImportedPages( $expectedWikiPages );
		}
	}

	/**
	 * Gets list of wiki pages with their content from specified manifests.
	 *
	 * @param array $manifestList List of manifests, just an array with paths
	 * @param string $langCode Import language code, to get only pages in specified language
	 *
	 * @return array Key is page title, value is "sha1" of page content
	 */
	private function getExpectedWikiPages(
		array $manifestList,
		string $langCode
	): array {
		$expectedWikiPages = [];

		foreach ( $manifestList as $absoluteManifestPath ) {
			$manifest = json_decode( file_get_contents( $absoluteManifestPath ), true );

			foreach ( $manifest as $pageTitle => $pageData ) {
				if ( $pageData['lang'] !== $langCode ) {
					continue;
				}

				$targetTitle = $pageData['target_title'];

				$expectedWikiPages[$targetTitle] = $pageData['sha1'];
			}
		}

		return $expectedWikiPages;
	}

	/**
	 * Checks if specified wiki pages are imported into DB.
	 *
	 * @param array $expectedWikiPages Array with expected wiki pages to check.
	 * 		Key is page title, value is "sha1" hash of its content
	 */
	private function checkImportedPages( array $expectedWikiPages ): void {
		$importedPages = [];

		foreach ( $expectedWikiPages as $title => $contentHash ) {
			// If there is just name title - it's "Main" namespace
			$ns = 0;
			$pageTitle = $title;

			// Otherwise we need to get namespace index
			if ( strpos( $title, ':' ) !== false ) {
				list( $nsName, $pageTitle ) = explode( ':', $title );

				$ns = \BsNamespaceHelper::getNamespaceIndex( $nsName );
			}

			$pageId = $this->db->selectField(
				'page',
				'page_id',
				[
					'page_title' => $pageTitle,
					'page_namespace' => $ns
				]
			);

			if ( $pageId ) {
				$titleObj = Title::newFromID( $pageId );
				$importedPages[$title] = $this->getContentHash( $titleObj );
			}
		}

		$this->assertArrayEquals( $expectedWikiPages, $importedPages, false, true );
	}

	/**
	 * Gets SHA1-hash of the latest revision content of specified title
	 *
	 * @param Title $title Processing title
	 * @return string SHA1-hash of page's the latest revision content,
	 * 		or empty string if content was not recognized
	 */
	private function getContentHash( Title $title ): string {
		$text = $this->getPageContent( $title );
		if ( $text ) {
			return sha1( $text );
		}

		return '';
	}

	/**
	 * Gets wikitext content of the latest revision of specified title
	 *
	 * @param Title $title Processing title
	 * @return string Wikitext content of page's the latest revision,
	 * 		or empty string if content was not recognized
	 */
	private function getPageContent( Title $title ): string {
		$wikiPage = WikiPage::factory( $title );

		$updater = $wikiPage->newPageUpdater( $this->getTestSysop()->getUser() );

		$parentRevision = $updater->grabParentRevision();
		$content = $parentRevision->getContent( SlotRecord::MAIN );
		if ( $content instanceof TextContent ) {
			$text = $content->getText();

			return $text;
		}

		return '';
	}

}
