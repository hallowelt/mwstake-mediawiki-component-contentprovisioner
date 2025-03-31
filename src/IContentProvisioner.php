<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MediaWiki\Status\Status;

interface IContentProvisioner {

	/**
	 * Manifest list provider setter.
	 * Used to get all manifests which should be processed by that provisioner.
	 * Usually injected in {@link ContentProvisionerPipeline}.
	 *
	 * @param IManifestListProvider $manifestListProvider
	 */
	public function setManifestListProvider( IManifestListProvider $manifestListProvider ): void;

	/**
	 * Gets list of manifests and processes them one by one.
	 * Contains all necessary import logic for specific "content provisioner" implementation.
	 *
	 * @return Status Good or bad status containing possible warnings or errors
	 */
	public function provision(): Status;
}
