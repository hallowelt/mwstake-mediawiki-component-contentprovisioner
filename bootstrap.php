<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION', '2.0.1' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'contentprovisioner', static function () {
	$GLOBALS['mwsgContentProvisionerSkip'] = [];

	$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] =
		'\\MWStake\\MediaWiki\\Component\\ContentProvisioner\\'
		. 'MediaWiki\\Hook\\LoadExtensionSchemaUpdates\\RunUpdate::callback';
} );
