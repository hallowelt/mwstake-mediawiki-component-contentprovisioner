<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Tests\Unit\ManifestListProvider;

use MediaWikiUnitTestCase;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider
 */
class StaticManifestProviderTest extends MediaWikiUnitTestCase {

	/**
	 * Get manifests with specific manifests key
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider::provideManifests
	 */
	public function testSuccess() {
		$path = __DIR__ . '/data/wiki_root';

		$manifestListProvider = new StaticManifestProvider(
			[
				'DistributionExtension',
				'DistributionExtension2',
				'DistributionExtension3'
			],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests(
			'ManifestsKey'
		);

		$expectedManifests = [
			"$path/path/to/manifest1.json",
			"$path/path/to/manifest2.json",
			"$path/path/to/manifest3.json",
			"$path/path/to/manifest4.json"
		];

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	/**
	 * Get ALL registered manifests
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider::provideManifests
	 */
	public function testAllManifests() {
		$path = __DIR__ . '/data/wiki_root';

		$manifestListProvider = new StaticManifestProvider(
			[
				'DistributionExtension',
				'DistributionExtension2',
				'DistributionExtension3'
			],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$expectedManifests = [
			'ManifestsKey' => [
				"$path/path/to/manifest1.json",
				"$path/path/to/manifest2.json",
				"$path/path/to/manifest3.json",
				"$path/path/to/manifest4.json"
			],
			'DifferentManifestsKey' => [
				"$path/path/to/manifest5.json"
			]
		];

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	/**
	 * Wrong installation path provided, no manifests found
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider::provideManifests
	 */
	public function testWrongPath() {
		$path = __DIR__ . '/data/wrong_path';

		$manifestListProvider = new StaticManifestProvider(
			[
				'DistributionExtension',
				'DistributionExtension2'
			],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests(
			'ManifestsKey'
		);

		$this->assertEquals( [], $actualManifests );
	}

	/**
	 * No enabled extensions, therefore no manifests found
	 *
	 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider::provideManifests
	 */
	public function testNoExtensions() {
		$path = __DIR__ . '/data/wrong_path';

		$manifestListProvider = new StaticManifestProvider( [], $path );

		$actualManifests = $manifestListProvider->provideManifests(
			'ManifestsKey',
		);

		$this->assertEquals( [], $actualManifests );
	}
}
