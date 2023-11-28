<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MWStake\MediaWiki\Component\ContentProvisioner\EntitySync\WikiPageSync;
use Status;

/**
 * Base class for entities synchronization.
 *
 * @see WikiPageSync
 */
abstract class EntitySync {
	use UpdateLogStorageTrait;

	/**
	 * @param string $entryKey
	 * @param array $entityData
	 * @return Status
	 */
	public function sync( string $entryKey, array $entityData = [] ): Status {
		$status = $this->doSync( $entryKey );

		$entityKey = new EntityKey( $this->getProvisionerKey(), $entryKey );
		$this->upsertEntitySyncRecord( (string)$entityKey, $entityData );

		return $status;
	}

	/**
	 * All synchronization logic should be here.
	 *
	 * @param string $entityKey
	 * @return Status
	 */
	abstract protected function doSync( string $entityKey ): Status;

	/**
	 * Get key of content provisioner, associated with entity which should be synced.
	 * For example, in case with {@link WikiPageSync} it will be "DefaultContentProvisioner".
	 *
	 * @return string
	 * @see EntityKey::$provisionerKey
	 */
	abstract protected function getProvisionerKey(): string;
}
