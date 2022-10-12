<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

abstract class AttributeProvider {

	/**
	 * Arbitrary extension name which attributes are bind to.
	 */
	public const EXTENSION_NAME = 'MWStakeContentProvisioner';

	/**
	 *  List of enabled extensions.
	 * 	Used to iterate through their "extension.json" files to check for attributes values set.
	 *
	 * @var array
	 */
	protected $enabledExtensions;

	/**
	 * Wiki installation path.
	 * Used to find directory with extensions and to correctly iterate through them.
	 *
	 * @var string
	 */
	protected $installPath;

	/**
	 * @param array $enabledExtensions List of enabled extensions.
	 * 		{@see AttributeProvider::$enabledExtensions}
	 * @param string $installPath Wiki installation path.
	 * 		{@see AttributeProvider::$installPath}
	 */
	public function __construct( array $enabledExtensions, string $installPath ) {
		$this->enabledExtensions = $enabledExtensions;
		$this->installPath = $installPath;
	}

	/**
	 * Returns value of specified "extension.json" attribute.
	 * Merges values added to that attribute from all enabled extensions into one array.
	 *
	 * @param string $attributeName Name of attribute which value is needed
	 * @return array Attribute value as associative array or list of values
	 */
	protected function getAttribute( string $attributeName ): array {
		$attrValues = [];

		$extensionName = self::EXTENSION_NAME;

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
				// We need to check only attributes associated with specified extension
				if ( empty( $extManifest['attributes'][$extensionName] ) ) {
					continue;
				}

				$attributes = $extManifest['attributes'][$extensionName];

				if ( !empty( $attributes[$attributeName] ) ) {
					$attrValues = array_merge_recursive( $attrValues, $attributes[$attributeName] );
				}
			}
		}

		return $attrValues;
	}
}
