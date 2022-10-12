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
		foreach ( $allManifests as $manifestKey => &$manifestList ) {
			$manifestList = array_unique( $manifestList );
		}

		if ( $manifestsKey === '' ) {
			return $allManifests;
		}

		if ( !empty( $allManifests[$manifestsKey] ) ) {
			return $allManifests[$manifestsKey];
		}

		return [];
	}
}
