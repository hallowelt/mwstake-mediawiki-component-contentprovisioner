<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use MWStake\MediaWiki\Component\ContentProvisioner\Output\NullOutput;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Status;
use Wikimedia\ObjectFactory;

class ContentProvisionerPipeline implements LoggerAwareInterface, OutputAwareInterface {

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory;

	/**
	 * @var IContentProvisionerRegistry
	 */
	private $contentProvisionerRegistry;

	/**
	 * @var IContentProvisioner[]
	 */
	private $contentProvisioners = [];

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @param ObjectFactory $objectFactory
	 * @param IContentProvisionerRegistry $contentProvisionerRegistry
	 * @param bool $executeDefaultProvisioner
	 * 		<tt>true</tt> if default content provisioner should be executed,
	 * 		<tt>false</tt> otherwise. See {@link ContentProvisioner}
	 */
	public function __construct(
		ObjectFactory $objectFactory,
		IContentProvisionerRegistry $contentProvisionerRegistry,
		bool $executeDefaultProvisioner = true
	) {
		$this->logger = new NullLogger();
		$this->output = new NullOutput();

		$this->objectFactory = $objectFactory;

		$this->contentProvisionerRegistry = $contentProvisionerRegistry;

		if ( $executeDefaultProvisioner ) {
			$this->contentProvisioners[] = ContentProvisioner::factory(
				'DefaultContentProvisioner',
				$this->contentProvisionerRegistry->getManifestListProvider()
			);
		}
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output ): void {
		$this->output = $output;
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

			// Pipeline will force its output into content provisioners
			if ( $contentProvisioner instanceof OutputAwareInterface ) {
				$contentProvisioner->setOutput( $this->output );
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
		$contentProvisioners = $this->contentProvisionerRegistry->getProvisioners();

		$manifestListProvider = $this->contentProvisionerRegistry->getManifestListProvider();

		foreach ( $contentProvisioners as $provisionerKey => $contentProvisioner ) {
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
			$contentProvisionerSpecs['args'][] = $manifestListProvider;

			$contentProvisionerObj = $this->objectFactory->createObject( $contentProvisionerSpecs );

			// Check if object is instance of "IContentProvisioner"
			if ( !$contentProvisionerObj instanceof IContentProvisioner ) {
				$this->logger->warning( "Content provisioner \"$provisionerKey\" " .
					"must implement \"IContentProvisioner\" interface!" );
				continue;
			}

			$this->contentProvisioners[] = $contentProvisionerObj;
		}
	}

}
