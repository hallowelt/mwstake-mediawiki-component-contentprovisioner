<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ContentProvisioner\EntitySync\WikiPageSync;

return [
	'ContentProvisionerWikiPageSync' => static function ( MediaWikiServices $services ) {
		$wikiPageSync = new WikiPageSync(
			$services->getTitleFactory(),
			$services->getWikiPageFactory(),
			$services->getContentLanguage(),
			$services->getLanguageFallback()
		);

		$wikiPageSync->setLogger( LoggerFactory::getInstance( 'ContentProvisioner_Sync' ) );

		return $wikiPageSync;
	}
];
