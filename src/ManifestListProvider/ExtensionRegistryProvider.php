<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use ExtensionRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class ExtensionRegistryProvider implements IManifestListProvider {

	/**
	 * @var string
	 */
	private $attributeName;

	/**
	 * @var ExtensionRegistry
	 */
	private $extensionRegistry;

	/**
	 * @param string $attributeName
	 * @param ExtensionRegistry $extensionRegistry
	 */
	public function __construct( string $attributeName, ExtensionRegistry $extensionRegistry ) {
		$this->attributeName = $attributeName;
		$this->extensionRegistry = $extensionRegistry;
	}

	/**
	 * @inheritDoc
	 */
	public function provideManifests(): array {
		$manifestsList = $this->extensionRegistry->getAttribute( $this->attributeName );

		return $manifestsList;
	}

}
