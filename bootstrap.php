<?php

use MWStake\MediaWiki\Component\ContentProvisioner\MediaWiki\Hook\LoadExtensionSchemaUpdates\RunUpdate;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_CONTENTPROVISIONER_VERSION', '3.0.2' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'contentprovisioner', static function () {
	$GLOBALS['mwsgContentProvisionerSkip'] = [];

	$GLOBALS['wgExtensionFunctions'][] = static function () {
		$hookContainer = \MediaWiki\MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->register( 'LoadExtensionSchemaUpdates', [ RunUpdate::class, 'callback' ] );
	};

	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';
} );
