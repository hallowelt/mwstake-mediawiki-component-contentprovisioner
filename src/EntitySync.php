<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use Status;

/**
 * Base class for entities synchronization.
 *
 * @see \MWStake\MediaWiki\Component\ContentProvisioner\EntitySync\WikiPageSync
 */
abstract class EntitySync {
	use UpdateLogStorageTrait;

	/**
	 * @param string $entityKey
	 * @param array $entityData
	 * @return Status
	 */
	public function sync( string $entityKey, array $entityData = [] ): Status {
		$status = $this->doSync( $entityKey );
		$this->upsertEntitySyncRecord( $entityKey, $entityData );

		return $status;
	}

	/**
	 * All synchronization logic should be here.
	 *
	 * @param string $entityKey
	 * @return Status
	 */
	abstract protected function doSync( string $entityKey ): Status;
}
