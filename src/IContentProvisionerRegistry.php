<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

interface IContentProvisionerRegistry {

	/**
	 * @return IManifestListProvider
	 */
	public function getManifestListProvider(): IManifestListProvider;

	/**
	 * @return array
	 */
	public function getProvisioners(): array;
}
