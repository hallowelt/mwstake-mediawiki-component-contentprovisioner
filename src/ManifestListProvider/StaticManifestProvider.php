<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\AttributeProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;

class StaticManifestProvider extends AttributeProvider implements IManifestListProvider {

	/**
	 * Name of "extension.json" attribute where content manifests are got from.
	 *
	 * @var string
	 */
	private $attributeName = 'ContentManifests';

	/**
	 * @inheritDoc
	 */
	public function provideManifests( string $manifestsKey = '' ): array {
		$allManifests = $this->getAttribute( $this->attributeName );

		// Remove duplicates
		$allManifests = $this->removeDuplicates( $allManifests );
		// Expand paths from relative to absolute
		$allManifests = $this->expandPaths( $allManifests );

		if ( $manifestsKey === '' ) {
			return $allManifests;
		}

		if ( !empty( $allManifests[$manifestsKey] ) ) {
			return $allManifests[$manifestsKey];
		}

		return [];
	}

	/**
	 * @param array $allManifests
	 *
	 * @return array
	 */
	private function removeDuplicates( array $allManifests ): array {
		foreach ( $allManifests as $manifestKey => $manifestList ) {
			$allManifests[$manifestKey] = array_unique( $manifestList );
		}

		return $allManifests;
	}

	/**
	 * @param array $allManifests
	 *
	 * @return array
	 */
	private function expandPaths( array $allManifests ): array {
		foreach ( $allManifests as $manifestKey => $manifestList ) {
			foreach ( $manifestList as $i => $manifestPath ) {
				$allManifests[$manifestKey][$i] = $this->installPath . '/' . $manifestPath;
			}
		}

		return $allManifests;
	}
}
