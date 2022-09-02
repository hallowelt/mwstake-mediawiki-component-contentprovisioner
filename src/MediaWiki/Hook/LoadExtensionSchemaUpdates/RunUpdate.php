<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Hook\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Maintenance\ProvisionContents;

class RunUpdate implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addPostDatabaseUpdateMaintenance(
			ProvisionContents::class
		);

		return true;
	}
}
