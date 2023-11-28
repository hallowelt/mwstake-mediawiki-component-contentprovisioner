<?php

namespace MWStake\MediaWiki\Component\ContentProvisioner;

class EntityKey {

	/**
	 * Key of content provisioner which is associated with specific entity.
	 * That key is also used in "extension.json".
	 * If more specifically, in "MWStakeContentProvisionerContentProvisioners" attribute.
	 *
	 * For example, for wiki page provisioner key will be "DefaultContentProvisioner"
	 *
	 * @var string
	 */
	private $provisionerKey;

	/**
	 * Key which describes specific entry of some entity.
	 *
	 * For example, for wiki page entry key would be prefixed DB key.
	 *
	 * @var string
	 */
	private $entryKey;

	/**
	 * @param string $provisionerKey
	 * @param string $entryKey
	 */
	public function __construct( string $provisionerKey, string $entryKey ) {
		$this->provisionerKey = $provisionerKey;
		$this->entryKey = $entryKey;
	}

	/**
	 * Entity key is used in "sync records" in "updatelog" table.
	 * "ul_key" column of this table has limit of 255 chars.
	 *
	 * But in some cases concatenation of "provisioner key" and "entry key" may exceed 255 chars.
	 * For example, wiki page prefixed DB title can be up to 255 chars (that's actually "entry key" for wiki pages),
	 * so concatenation of that "entry key" and "provisioner key" may exceed 255 chars.
	 *
	 * That's why it was decided to store SHA1 of such concatenation instead.
	 *
	 * @return string
	 */
	public function __toString() {
		return sha1( $this->provisionerKey . ':' . $this->entryKey );
	}
}
