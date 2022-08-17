<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Test\ManifestListProvider;

use ExtensionRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\ExtensionRegistryProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\ExtensionRegistryProvider
 */
class ExtensionRegistryProviderTest extends TestCase {

	public function testSuccess() {
		$expectedManifests = [
			"path/to/manifest1.json",
			"path/to/manifest2.json"
		];

		$extensionRegistryMock = $this->createMock( ExtensionRegistry::class );
		$extensionRegistryMock->method( 'getAttribute' )->willReturn( $expectedManifests );

		$manifestListProvider = new ExtensionRegistryProvider(
			'SomeExtensionContentManifests',
			$extensionRegistryMock
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$this->assertEquals( $expectedManifests, $actualManifests );
	}

	public function testEmptyAttribute() {
		$extensionRegistryMock = $this->createMock( ExtensionRegistry::class );
		$extensionRegistryMock->method( 'getAttribute' )->willReturn( [] );

		$manifestListProvider = new ExtensionRegistryProvider(
			'SomeExtensionContentManifests',
			$extensionRegistryMock
		);

		$actualManifests = $manifestListProvider->provideManifests();

		$this->assertEquals( [], $actualManifests );
	}
}
