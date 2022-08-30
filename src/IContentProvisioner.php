<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use Status;

interface IContentProvisioner {

	/**
	 * @param IManifestListProvider $manifestListProvider
	 *
	 * @return IContentProvisioner
	 */
	public static function factory( IManifestListProvider $manifestListProvider ): IContentProvisioner;

	/**
	 * Gets list of manifests and processes them one by one.
	 * Contains all necessary import logic for specific "content provisioner" implementation.
	 *
	 * @return Status
	 */
	public function provision(): Status;
}
