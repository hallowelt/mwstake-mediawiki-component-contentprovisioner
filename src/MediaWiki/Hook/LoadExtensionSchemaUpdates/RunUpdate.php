<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Hook\LoadExtensionSchemaUpdates;

use DatabaseUpdater;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Maintenance\ProvisionContents;

class RunUpdate implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$provider = new static();
		return $provider->onLoadExtensionSchemaUpdates( $updater );
	}

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
