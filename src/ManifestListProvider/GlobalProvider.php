<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class GlobalProvider implements IManifestListProvider {

	/**
	 * @inheritDoc
	 */
	public function provideManifests( string $extensionName, string $attributeName ) : array {
		return $GLOBALS['mwsgContentManifests'][$extensionName];
	}
}
