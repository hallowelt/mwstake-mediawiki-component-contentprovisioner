<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

class ContentProvisionerProvider extends AttributeProvider implements IContentProvisionerProvider {

	private $attributeName = 'ContentProvisioners';

	/**
	 * @inheritDoc
	 */
	public function getProvisioners(): array {
		return $this->getAttribute( $this->attributeName );
	}
}
