<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Maintenance;

use MediaWiki\Logger\ConsoleLogger;
use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerPipeline;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\GlobalProvider;

class ProvisionContents extends \LoggedUpdateMaintenance {

	/**
	 * @inheritDoc
	 */
	protected function doDBUpdates() {
		$contentProvisionerPipeline = new ContentProvisionerPipeline( new GlobalProvider() );
		$contentProvisionerPipeline->setLogger( new ConsoleLogger( 'ContentProvisioner' ) );

		$contentProvisionerPipeline->execute();

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUpdateKey() {
		return 'ContentProvisioner_' . $this->calculateManifestsHash();
	}

	/**
	 * @inheritDoc
	 */
	protected function updateSkippedMessage() {
		return 'ContentProvisioner: No changes in manifests. Skipping...';
	}

	/**
	 *
	 * Concatenates content of all registered manifests and calculates its MD5 hash.
	 * It is used to create dynamic "update key".
	 * So update key will stay the same (so this script will be skipped) until some manifest changes.
	 *
	 * TODO: Probably each content provisioner should calculate hash for its own manifests
	 * But then we will need to execute only specific content provisioners in pipeline...
	 *
	 * @return string MD5 hash
	 */
	private function calculateManifestsHash(): string {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $IP;

		$manifestsContent = '';

		$manifestsList = $this->getAllManifests();
		foreach ( $manifestsList as $manifestPath ) {
			$absoluteManifestPath = $IP . '/' . $manifestPath;

			$manifestsContent .= file_get_contents( $absoluteManifestPath );
		}

		return md5( $manifestsContent );
	}

	/**
	 * Get all registered for import manifests
	 *
	 * @return array List with manifests' paths
	 */
	private function getAllManifests(): array {
		// Probably we can add a public method to IContentProvisioner interface
		// to get all manifests related to specific ContentProvisioner.
		// Then we will be able to run through all content provisioners here,
		// get manifest list for each of them, and collect all manifests.

		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $mwsgContentManifests;

		$manifestsList = [];

		foreach ( $mwsgContentManifests as $extensionName => $extensionManifests ) {
			$manifestsList = array_merge( $manifestsList, $extensionManifests );
		}

		return array_unique( $manifestsList );
	}

}
