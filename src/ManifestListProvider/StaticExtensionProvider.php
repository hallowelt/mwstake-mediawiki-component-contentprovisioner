<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class StaticExtensionProvider implements IManifestListProvider {

	/**
	 * @var array
	 */
	private $enabledExtensions;

	/**
	 * @var string
	 */
	private $installPath;

	/**
	 * @param array $enabledExtensions
	 * @param string $installPath
	 */
	public function __construct( array $enabledExtensions, string $installPath ) {
		$this->enabledExtensions = $enabledExtensions;
		$this->installPath = $installPath;
	}

	/**
	 * @inheritDoc
	 */
	public function provideManifests( string $extensionName, string $attributeName ): array {
		$manifestsList = [];

		foreach ( $this->enabledExtensions as $extName ) {
			$extPath = "{$this->installPath}/extensions/$extName/extension.json";

			if ( !file_exists( $extPath ) ) {
				continue;
			}

			$extManifestRaw = file_get_contents( $extPath );
			if ( !$extManifestRaw ) {
				continue;
			}

			$extManifest = json_decode( $extManifestRaw, true );
			if ( $extManifest && !empty( $extManifest['attributes'] ) ) {
				foreach ( $extManifest['attributes'] as $extName => $attributes ) {
					// We need to check only attributes associated with specified extension
					if ( $extName !== $extensionName ) {
						continue;
					}

					if ( $attributes[$attributeName] ) {
						$manifestsList = array_merge( $manifestsList, $attributes[$attributeName] );
					}
				}
			}
		}

		return $manifestsList;
	}
}
