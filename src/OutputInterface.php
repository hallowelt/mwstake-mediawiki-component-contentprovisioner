<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface OutputInterface {

	/**
	 * Sets the verbosity of the output.
	 * <tt>true</tt> if output is needed, <tt>false</tt> otherwise.
	 *
	 * @param bool $verbose
	 */
	public function setVerbosity( bool $verbose ): void;

	/**
	 * Outputs specified message.
	 *
	 * @param string $message Message to output
	 */
	public function write( string $message ): void;
}
