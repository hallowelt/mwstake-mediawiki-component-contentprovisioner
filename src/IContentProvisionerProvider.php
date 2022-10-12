<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface IContentProvisionerProvider {

	/**
	 * @return array
	 */
	public function getProvisioners(): array;
}
