<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION', '1.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'contentprovisioner', static function () {
	// Allow setting on LocalSetting level
	if ( !isset( $GLOBALS['mwsgContentProvisioners'] ) ) {
		$GLOBALS['mwsgContentProvisioners'] = [];
	}

	// Allow setting on LocalSetting level
	if ( !isset( $GLOBALS['mwsgContentManifests'] ) ) {
		$GLOBALS['mwsgContentManifests'] = [];
	}

	// Add basic "ContentProvisioner"
	$GLOBALS['mwsgContentProvisioners']['ContentProvisioner'] = [
		'factory' => '\MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisioner::factory'
	];

	$GLOBALS['mwsgContentManifests']['ContentProvisioner'] = [];

	$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] =
		// phpcs:ignore Generic.Files.LineLength.TooLong
		'\\MWStake\\MediaWiki\\Component\\ContentProvisioner\\MediaWiki\\Hook\\LoadExtensionSchemaUpdates\\RunUpdate::onLoadExtensionSchemaUpdates';
} );
