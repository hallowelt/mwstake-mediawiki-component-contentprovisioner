<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface IManifestListProvider {

	/**
	 * Returns list of manifest files which should be processed by specific content provisioner.
	 * Each manifest describes list of wiki pages (with some metadata) to be imported into wiki.
	 * These manifests are used during wiki update or installation to import some content into wiki.
	 *
	 * @param string $manifestsKey Key to recognize manifests needed
	 *
	 * @return array List of manifest files
	 */
	public function provideManifests( string $manifestsKey ): array;
}
