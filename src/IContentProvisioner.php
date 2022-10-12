<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use Status;

interface IContentProvisioner {

	/**
	 * @param string $manifestsKey
	 * 		Helps to recognize manifests which should be processed by that provisioner.
	 *		Used in {@link IManifestListProvider}.
	 * @param IManifestListProvider $manifestListProvider
	 * 		Used to get all manifests which should be processed by that provisioner.
	 * 		Usually injected by {@link ContentProvisionerPipeline}.
	 *
	 * @return IContentProvisioner
	 */
	public static function factory(
		string $manifestsKey,
		IManifestListProvider $manifestListProvider
	): IContentProvisioner;

	/**
	 * Gets list of manifests and processes them one by one.
	 * Contains all necessary import logic for specific "content provisioner" implementation.
	 *
	 * @return Status
	 */
	public function provision(): Status;
}
