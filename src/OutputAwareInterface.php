<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface OutputAwareInterface {

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output ): void;
}
