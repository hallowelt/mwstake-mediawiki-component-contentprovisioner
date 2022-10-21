<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION', '1.0.3' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'contentprovisioner', static function () {
	$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] =
		'\\MWStake\\MediaWiki\\Component\\ContentProvisioner\\'
		. 'MediaWiki\\Hook\\LoadExtensionSchemaUpdates\\RunUpdate::callback';
} );
