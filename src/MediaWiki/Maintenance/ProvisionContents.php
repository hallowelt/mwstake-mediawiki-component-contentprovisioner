<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Maintenance;

use ExtensionRegistry;
use LoggedUpdateMaintenance;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerPipeline;
use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerRegistry\FileBasedRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\Output\PrintOutput;

class ProvisionContents extends LoggedUpdateMaintenance {

	/**
	 * @inheritDoc
	 */
	protected function doDBUpdates() {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $IP;

		$enabledExtensions = array_keys( ExtensionRegistry::getInstance()->getAllThings() );

		$contentProvisionerRegistry = new FileBasedRegistry( $enabledExtensions, $IP );

		$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();

		$contentProvisionerPipeline = new ContentProvisionerPipeline(
			$objectFactory,
			$contentProvisionerRegistry
		);
		$contentProvisionerPipeline->setLogger( LoggerFactory::getInstance( 'ContentProvisioner' ) );
		$contentProvisionerPipeline->setOutput( new PrintOutput() );

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
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $IP;

		$enabledExtensions = array_keys( ExtensionRegistry::getInstance()->getAllThings() );

		$contentProvisionerRegistry = new FileBasedRegistry( $enabledExtensions, $IP );
		$manifestsListProvider = $contentProvisionerRegistry->getManifestListProvider();

		$manifestsList = [];

		$allManifests = $manifestsListProvider->provideManifests();

		foreach ( $allManifests as $manifestKey => $extensionManifests ) {
			$manifestsList = array_merge( $manifestsList, $extensionManifests );
		}

		return array_unique( $manifestsList );
	}

}
