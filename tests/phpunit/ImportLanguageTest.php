<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Test;

use MediaWiki\Languages\LanguageFallback;
use MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage
 */
class ImportLanguageTest extends TestCase {

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
		$languageFallbackMock = $this->createMock( LanguageFallback::class );
		$languageFallbackMock->method( 'getAll' )->willReturnMap(
			[
				[ 'en', 0, [ 'en' ] ],
				[ 'de', 0, [ 'en' ] ],
				[ 'de-formal', 0, [ 'de', 'en' ] ],
				[ 'ru', 0, [ 'en' ] ],
				[ 'pt', 0, [ 'pt-br', 'en' ] ],
				[ 'gl', 0, [ 'pt', 'en' ] ],
				[ 'unknown', 0, [ 'en' ] ],
			]
		);

		$importLanguage = new ImportLanguage( $languageFallbackMock, $wikiContentLanguage );
		$availableLanguages = [ 'en', 'de', 'pt-br' ];

		$actualImportLanguage = $importLanguage->getImportLanguage( $availableLanguages );

		$this->assertEquals( $expectedImportLanguage, $actualImportLanguage );
	}
}
