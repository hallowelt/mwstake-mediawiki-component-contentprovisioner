<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

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
	private function entityExists( string $entityKey ): bool {
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
	 * @return void
	 * @see IDatabase::upsert()
	 */
	private function upsertEntity( string $entityKey ): void {
		$row = [
			'ul_key' => $entityKey,
			'ul_value' => wfTimestampNow()
		];

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$db->upsert(
			'updatelog',
			$row,
			'ul_key',
			$row
		);
	}

	/**
	 * @param string $entityKey
	 * @return string
	 */
	private function getTimestamp( string $entityKey ): string {
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$timestamp = $db->selectField(
			'updatelog',
			'ul_value',
			[
				'ul_key' => $entityKey
			]
		);

		return $timestamp;
	}
}
