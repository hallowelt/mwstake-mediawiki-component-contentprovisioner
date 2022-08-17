<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Test\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticExtensionProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticExtensionProvider
 */
class StaticExtensionProviderTest extends TestCase {

	public function testSuccess() {
		$path = __DIR__ . '/data/wiki_root';

		$manifestListProvider = new StaticExtensionProvider(
			'ContentManifests',
			[
				'DistributionExtension',
				'DistributionExtension2'
			],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$expectedManifests = [
			"path/to/manifest1.json",
			"path/to/manifest2.json",
			"path/to/manifest3.json",
			"path/to/manifest4.json"
		];

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	public function testWrongPath() {
		$path = __DIR__ . '/data/wrong_path';

		$manifestListProvider = new StaticExtensionProvider(
			'ContentManifests',
			[
				'DistributionExtension',
				'DistributionExtension2'
			],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$this->assertEquals( [], $actualManifests );
	}

	public function testNoExtensions() {
		$path = __DIR__ . '/data/wrong_path';

		$manifestListProvider = new StaticExtensionProvider(
			'ContentManifests',
			[],
			$path
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$this->assertEquals( [], $actualManifests );
	}
}
