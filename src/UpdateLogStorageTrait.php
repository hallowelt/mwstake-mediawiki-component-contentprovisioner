<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MediaWiki\MediaWikiServices;

/**
 * Used in cases when we need to store information about imported entities in the "updatelog" table.
 * It can be done to track already imported entities.
 *
 * "entityKey" here is unique key which should be used to identify each separate imported entity.
 * For example, for wiki pages it could be prefixed db key
 * (and probably some string ID of content provisioner).
 */
trait UpdateLogStorageTrait {

	/**
	 * @param string $entityKey
	 * @return bool
	 */
	private function entityWasSynced( string $entityKey ): bool {
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$exists = $db->selectField(
			'updatelog',
			'ul_key',
			[
				'ul_key' => $entityKey
			]
		);

		return $exists;
	}

	/**
	 * If entity exists - timestamp in stored "ul_value" field will be updated with current timestamp.
	 * If entity does not exist - it is added with current timestamp.
	 *
	 * @param string $entityKey
	 * @param array $entityData Some arbitrary data which can be stored in "updatelog.ul_value".
	 * 		By default - latest entity sync timestamp, under "timestamp" key.
	 * @return void
	 * @see IDatabase::upsert()
	 */
	private function upsertEntitySyncRecord( string $entityKey, array $entityData = [] ): void {
		$row = [
			'ul_key' => $entityKey
		];

		if ( !$entityData ) {
			$entityData['timestamp'] = wfTimestampNow();
		}

		$row['ul_value'] = json_encode( $entityData );

		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$dbw->upsert(
			'updatelog',
			$row,
			'ul_key',
			$row
		);
	}

	/**
	 * Gets some entity arbitrary data, which can be stored in "updatelog.ul_value".
	 * Data stored there must be JSON encoded.
	 * By default - there is the latest entity sync timestamp, under "timestamp" array key.
	 *
	 * @param string $entityKey
	 * @return array
	 */
	private function getEntitySyncData( string $entityKey ): array {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$entityDataRaw = $dbr->selectField(
			'updatelog',
			'ul_value',
			[
				'ul_key' => $entityKey
			]
		);

		$entityData = json_decode( $entityDataRaw, true );

		return $entityData;
	}
}
