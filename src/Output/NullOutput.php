<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\Output;

use MWStake\MediaWiki\Component\ContentProvisioner\OutputInterface;

/**
 * Stub for cases when no output is needed (or output is not yet set).
 */
class NullOutput implements OutputInterface {

	/**
	 * @inheritDoc
	 */
	public function setVerbosity( bool $verbose ): void {
		// Do nothing
	}

	/**
	 * @inheritDoc
	 */
	public function write( string $message ): void {
		// Do nothing
	}
}
