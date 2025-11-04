<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Tests\Unit\ContentProvisionerProvider;

use MediaWikiUnitTestCase;
use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider
 */
class ContentProvisionerProviderTest extends MediaWikiUnitTestCase {

	/**
	 * Get all registered content provisioners
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider::getProvisioners
	 */
	public function testGetAllProvisioners() {
		$path = __DIR__ . '/data/wiki_root';

		$contentProvisionerProvider = new ContentProvisionerProvider(
			[
				'DistributionExtension',
				'DistributionExtension2'
			],
			$path
		);

		$actualProvisionersSpecs = $contentProvisionerProvider->getProvisioners();

		$expectedProvisionersSpecs = [
			'SomeArbitraryProvisioner' => [
				'factory' => '\\Some\\Namespace\\SomeArbitraryProvisioner::factory',
				'args' => [
					'SomeArbitraryManifestsKey',
					'ArbitraryArgument'
				],
				'services' => [
					'SomeArbitraryService1',
					'SomeArbitraryService2',
					'SomeArbitraryService3'
				]
			],
			'SomeArbitraryProvisioner2' => [
				'factory' => '\\Some\\Namespace\\SomeArbitraryProvisioner2::factory',
				'args' => [
					'SomeArbitraryManifestsKey2'
				],
				'services' => [
					'SomeArbitraryService1',
					'SomeArbitraryService4'
				]
			],
		];

		$this->assertEquals( $expectedProvisionersSpecs, $actualProvisionersSpecs );
	}

}
