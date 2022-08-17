<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class StaticExtensionProvider implements IManifestListProvider {

	/**
	 * @var string
	 */
	private $attributeName;

	/**
	 * @var array
	 */
	private $enabledExtensions;

	/**
	 * @var string
	 */
	private $installPath;

	/**
	 * @param string $attributeName
	 * @param array $enabledExtensions
	 * @param string $installPath
	 */
	public function __construct( string $attributeName, array $enabledExtensions, string $installPath ) {
		$this->attributeName = $attributeName;
		$this->enabledExtensions = $enabledExtensions;
		$this->installPath = $installPath;
	}

	/**
	 * @inheritDoc
	 */
	public function provideManifests(): array {
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
					if ( $attributes[$this->attributeName] ) {
						$manifestsList = array_merge( $manifestsList, $attributes[$this->attributeName] );
					}
				}
			}
		}

		return $manifestsList;
	}
}
