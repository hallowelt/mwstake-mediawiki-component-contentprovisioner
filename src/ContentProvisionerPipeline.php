<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

use Exception;
use MWStake\MediaWiki\Component\ContentProvisioner\Output\NullOutput;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Status;
use Wikimedia\ObjectFactory\ObjectFactory;

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
	 * @var array
	 */
	private $contentProvisionerSkip;

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
	 * @param array $contentProvisionerSkip
	 */
	public function __construct(
		ObjectFactory $objectFactory,
		IContentProvisionerRegistry $contentProvisionerRegistry,
		array $contentProvisionerSkip = []
	) {
		$this->logger = new NullLogger();
		$this->output = new NullOutput();

		$this->objectFactory = $objectFactory;

		$this->contentProvisionerRegistry = $contentProvisionerRegistry;
		$this->contentProvisionerSkip = $contentProvisionerSkip;

		if ( !in_array( 'DefaultContentProvisioner', $this->contentProvisionerSkip ) ) {

			$defaultContentProvisionerSpec = [
				'class' => DefaultContentProvisioner::class,
				'args' => [
					'DefaultContentProvisioner'
				],
				'services' => [
					'ContentLanguage',
					'LanguageFallback',
					'TitleFactory'
				]
			];

			/** @var IContentProvisioner $defaultContentProvisioner */
			$defaultContentProvisioner = $this->objectFactory->createObject(
				$defaultContentProvisionerSpec
			);
			$defaultContentProvisioner->setManifestListProvider(
				$this->contentProvisionerRegistry->getManifestListProvider()
			);

			$this->contentProvisioners[] = $defaultContentProvisioner;
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

		foreach ( $contentProvisioners as $provisionerKey => $contentProvisionerData ) {
			$contentProvisionerSpecs = [];

			if ( in_array( $provisionerKey, $this->contentProvisionerSkip ) ) {
				$this->logger->debug( "Content provisioner \"$provisionerKey\" - skipped..." );
				continue;
			}

			if ( is_callable( $contentProvisionerData ) ) {
				$contentProvisionerSpecs['factory'] = $contentProvisionerData;
			} else {
				if (
					is_array( $contentProvisionerData ) &&
					( isset( $contentProvisionerData['factory'] ) || isset( $contentProvisionerData['class'] ) )
				) {
					$contentProvisionerSpecs = $contentProvisionerData;
				} else {
					$this->logger->warning( "Content provisioner \"$provisionerKey\" " .
						"must provide some callable!" );
					continue;
				}
			}

			try {
				$contentProvisionerObj = $this->objectFactory->createObject( $contentProvisionerSpecs );
			} catch ( Exception $e ) {
				$this->logger->error( "\"$provisionerKey\" content provisioner object failed to create," .
				"exception: {$e->getMessage()}" );

				continue;
			}

			// Check if object is instance of "IContentProvisioner"
			if ( !$contentProvisionerObj instanceof IContentProvisioner ) {
				$this->logger->warning( "Content provisioner \"$provisionerKey\" " .
					"must implement \"IContentProvisioner\" interface!" );
				continue;
			}

			// Inject manifest list provider into content provisioner
			$contentProvisionerObj->setManifestListProvider( $manifestListProvider );

			$this->contentProvisioners[] = $contentProvisionerObj;
		}
	}

}
