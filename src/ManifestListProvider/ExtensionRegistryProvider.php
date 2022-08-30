<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use ExtensionRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class ExtensionRegistryProvider implements IManifestListProvider {

	/**
	 * @var ExtensionRegistry
	 */
	private $extensionRegistry;

	/**
	 * @param ExtensionRegistry $extensionRegistry
	 */
	public function __construct( ExtensionRegistry $extensionRegistry ) {
		$this->extensionRegistry = $extensionRegistry;
	}

	/**
	 * @inheritDoc
	 */
	public function provideManifests( string $extensionName, string $attributeName ): array {
		$manifestsList = $this->extensionRegistry->getAttribute( $extensionName . $attributeName );

		return $manifestsList;
	}

}
