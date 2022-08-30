<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface IManifestListProvider {

	/**
	 * Returns list of manifest files.
	 * Each manifest describes list of wiki pages (with some metadata) to be imported into wiki.
	 * These manifests are used during wiki update or installation to import some content into wiki.
	 *
	 * @param string $extensionName Name of extension which attribute is associated with
	 * @param string $attributeName Name of attribute where manifests list is stored
	 * @return array List of manifest files
	 */
	public function provideManifests( string $extensionName, string $attributeName ): array;
}
