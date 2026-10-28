<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Tests\Unit;

use MediaWiki\Language\LanguageFallbackMode;
use MediaWiki\Languages\LanguageFallback;
use MediaWikiUnitTestCase;
use MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage
 */
class ImportLanguageTest extends MediaWikiUnitTestCase {

	/**
	 * @return array
	 */
	public function provideData() {
		return [
			'english' => [ 'en', 'en' ],
			'german' => [ 'de', 'de' ],
			'german formal' => [ 'de-formal', 'de' ],
			'russian' => [ 'ru', 'en' ],
			'portugal' => [ 'pt', 'pt-br' ],
			'unknown language' => [ 'unknown', 'en' ],
		];
	}

	/**
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage::getImportLanguage()
	 * @dataProvider provideData
	 */
	public function testSuccess( $wikiContentLanguage, $expectedImportLanguage ) {
		$mode = LanguageFallbackMode::MESSAGES;
		$languageFallbackMock = $this->createMock( LanguageFallback::class );
		$languageFallbackMock->method( 'getAll' )->willReturnMap(
			[
				[ 'en', $mode, [ 'en' ] ],
				[ 'de', $mode, [ 'en' ] ],
				[ 'de-formal', $mode, [ 'de', 'en' ] ],
				[ 'ru', $mode, [ 'en' ] ],
				[ 'pt', $mode, [ 'pt-br', 'en' ] ],
				[ 'gl', $mode, [ 'pt', 'en' ] ],
				[ 'unknown', $mode, [ 'en' ] ],
			]
		);

		$importLanguage = new ImportLanguage( $languageFallbackMock, $wikiContentLanguage );
		$availableLanguages = [ 'en', 'de', 'pt-br' ];

		$actualImportLanguage = $importLanguage->getImportLanguage( $availableLanguages );

		$this->assertEquals( $expectedImportLanguage, $actualImportLanguage );
	}
}
