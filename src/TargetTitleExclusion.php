<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

class TargetTitleExclusion {

	/**
	 * @param string $targetTitle
	 * @return bool
	 */
	public static function isExcluded( string $targetTitle ): bool {
		$excludedTargetTitles = $GLOBALS['mwsgContentProvisionerExcludedTargetTitles'] ?? [];

		return in_array( $targetTitle, $excludedTargetTitles, true );
	}
}
