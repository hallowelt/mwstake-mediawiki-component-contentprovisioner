<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Test\ContentProvisionerProvider;

// phpcs:ignore Generic.Files.LineLength.TooLong
use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider
 */
class ContentProvisionerProviderTest extends TestCase {

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
