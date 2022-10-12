<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerRegistry;

use MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider\ContentProvisionerProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisionerProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisionerRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider;

class FileBasedRegistry implements IContentProvisionerRegistry {

	/**
	 * @var IManifestListProvider
	 */
	private $manifestListProvider;

	/**
	 * @var IContentProvisionerProvider
	 */
	private $contentProvisionerProvider;

	/**
	 * @param array $enabledExtensions
	 * @param string $installPath
	 */
	public function __construct( array $enabledExtensions, string $installPath ) {
		$this->manifestListProvider = new StaticManifestProvider( $enabledExtensions, $installPath );
		$this->contentProvisionerProvider = new ContentProvisionerProvider(
			$enabledExtensions,
			$installPath
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getManifestListProvider() : IManifestListProvider {
		return $this->manifestListProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function getProvisioners(): array {
		return $this->contentProvisionerProvider->getProvisioners();
	}
}
