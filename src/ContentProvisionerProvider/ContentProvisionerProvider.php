<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner\ContentProvisionerProvider;

use MWStake\MediaWiki\Component\ContentProvisioner\AttributeProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisionerProvider;

/**
 * That manifest list provider is used to obtain
 * content provisioners from certain "extension.json" attribute.
 *
 * Attribute name can be overridden in subclasses, if needed.
 */
class ContentProvisionerProvider extends AttributeProvider implements IContentProvisionerProvider {

	/**
	 * Name of "extension.json" attribute where content provisioners are got from.
	 *
	 * @var string
	 */
	protected $attributeName = 'ContentProvisioners';

	/**
	 * @inheritDoc
	 */
	public function getProvisioners(): array {
		return $this->getAttribute( $this->attributeName );
	}
}
