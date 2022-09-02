<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Status;

class ContentProvisionerPipeline implements LoggerAwareInterface {

	/**
	 * @var IManifestListProvider
	 */
	private $manifestListProvider;

	/**
	 * @var IContentProvisioner[]
	 */
	private $contentProvisioners = [];

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param IManifestListProvider $manifestListProvider
	 */
	public function __construct( IManifestListProvider $manifestListProvider ) {
		$this->logger = new NullLogger();

		$this->manifestListProvider = $manifestListProvider;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * Collects all registered content provisioners and executes them one by one.
	 *
	 * @return Status
	 */
	public function execute(): Status {
		$this->collectContentProvisioners();

		$status = Status::newGood();

		foreach ( $this->contentProvisioners as $contentProvisioner ) {
			// Pipeline will force its logger into content provisioners
			if ( $contentProvisioner instanceof LoggerAwareInterface ) {
				$contentProvisioner->setLogger( $this->logger );
			}

			$provisionerStatus = $contentProvisioner->provision();
			$status->merge( $provisionerStatus );
		}

		return $status;
	}

	/**
	 * Goes through content provisioners' registry, creates and stores instance for each of them
	 */
	private function collectContentProvisioners(): void {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $mwsgContentProvisioners;

		$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();

		foreach ( $mwsgContentProvisioners as $provisionerKey => $contentProvisioner ) {
			$contentProvisionerSpecs = [];

			if ( is_callable( $contentProvisioner ) ) {
				$contentProvisionerSpecs['factory'] = $contentProvisioner;
			} else {
				if ( !is_callable( $contentProvisioner['factory'] ) ) {
					$this->logger->warning( "Content provisioner \"$provisionerKey\" " .
						"must provide some callable!" );
					continue;
				}

				$contentProvisionerSpecs = $contentProvisioner;
			}

			// Inject manifest list provider into "ContentProvisioner" factory
			$contentProvisionerSpecs = array_merge( $contentProvisionerSpecs, [
				'args' => [ $this->manifestListProvider ]
			] );

			$contentProvisionerObj = $objectFactory->createObject( $contentProvisionerSpecs );

			// Check if object is instance of "IContentProvisioner"
			if ( !$contentProvisionerObj instanceof IContentProvisioner ) {
				$this->logger->warning( "Content provisioner \"$provisionerKey\" " .
					"must implement \"IContentProvisioner\" interface!" );
			}

			$this->contentProvisioners[] = $contentProvisionerObj;
		}
	}

}
