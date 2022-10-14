<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Output;

use MWStake\MediaWiki\Component\ContentProvisioner\OutputInterface;

class PrintOutput implements OutputInterface {

	/**
	 * <tt>true</tt> if output is needed, <tt>false</tt> otherwise.
	 *
	 * @var bool
	 */
	private $verbose = true;

	/**
	 * @inheritDoc
	 */
	public function setVerbosity( bool $verbose ): void {
		$this->verbose = $verbose;
	}

	/**
	 * @inheritDoc
	 */
	public function write( string $message ): void {
		if ( !$this->verbose ) {
			return;
		}

		print $message;
	}
}
