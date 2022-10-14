<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\AttributeProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisionerProvider;

class ContentProvisionerProvider extends AttributeProvider implements IContentProvisionerProvider {

	/**
	 * Name of "extension.json" attribute where content provisioners are got from.
	 *
	 * @var string
	 */
	private $attributeName = 'ContentProvisioners';

	/**
	 * @inheritDoc
	 */
	public function getProvisioners(): array {
		return $this->getAttribute( $this->attributeName );
	}
}
