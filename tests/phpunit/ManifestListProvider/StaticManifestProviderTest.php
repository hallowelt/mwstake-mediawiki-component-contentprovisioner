<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Test\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider
 */
class StaticManifestProviderTest extends TestCase {

	/**
	 * Get manifests with specific manifests key
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
			"path/to/manifest1.json",
			"path/to/manifest2.json",
			"path/to/manifest3.json",
			"path/to/manifest4.json"
		];

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	/**
	 * Get ALL registered manifests
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
				"path/to/manifest1.json",
				"path/to/manifest2.json",
				"path/to/manifest3.json",
				"path/to/manifest4.json"
			],
			'DifferentManifestsKey' => [
				"path/to/manifest5.json"
			]
		];

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	/**
	 * Wrong installation path provided, no manifests found
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
