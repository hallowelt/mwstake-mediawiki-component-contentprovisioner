<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ContentProvisioner\EntitySync\WikiPageSync;

return [
	'ContentProvisionerWikiPageSync' => static function ( MediaWikiServices $services ) {
		$wikiPageSync = new WikiPageSync(
			$services->getTitleFactory(),
			$services->getWikiPageFactory()
		);

		$wikiPageSync->setLogger( LoggerFactory::getInstance( 'ContentProvisioner' ) );

		return $wikiPageSync;
	}
];
