# MediaWiki Stakeholders Group - Components
# ContentProvisioner for MediaWiki

Provides a mechanism which allows to import some arbitrary information during "maintenance/update.php".

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Usage in MediaWiki extension

Add `"mwstake/mediawiki-component-contentprovisioner": "~1.0"` to the `require` section of your `composer.json` file.

Explicit initialization is required. This can be achieved by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

### Register content to provision

Initially, content provisioner needs "manifest" file to get data to import from.
JSON file with such structure is needed:
```JSON
{
	"Some_page": {
		"lang": "de",
		"target_title": "Some_page",
		"content_path": "/pages/Main/Some_page.wiki",
		"sha1": "<hash_of_the_content>",
		"old_sha1": []
	},
	"Template:Some_template": {
		"lang": "en",
		"target_title": "Template:Some_template",
		"content_path": "/pages/Template/Some_template.wiki",
		"sha1": "<hash_of_the_content>",
		"old_sha1": []
	}
}
```

Here, "old_sha1" key contains hashes for previous content versions.
It is needed for cases with already existing wiki pages, to identify if they are just outdated or were added/changed by user.


Such files should be registered in "extension.json" of particular extension that way:
```json
{
	"attributes": {
		"MWStakeContentProvisioner": {
			"ContentManifests": {
				"DefaultContentProvisioner": [
					"extensions/SomeExtension/path/to/manifest.json"
				]
			}
		}
	}
}
```
Manifests added to "DefaultContentProvisioner" key will be processed by default content provisioner.
That content provisioner just imports corresponding wiki pages which are provided by manifest.

All registered files will be processed during next update with "maintenance/update.php".

## Custom content provisioners

Extensions may implement their own import logic within their own content provisioners.
To do so, it is needed to have a class, implementing "\MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisioner" interface.

### Register custom content provisioner

To be executed during "update.php", custom content provisioner must be registered in such way (ObjectFactory specification):
```json
{
	"attributes": {
		"MWStakeContentProvisioner": {
			"ContentProvisioners": {
				"ArbitraryContentProvisionerKey": {
					"class": "\\MediaWiki\\Path\\To\\ArbitraryProvisioner",
					"args": [
						"ManifestsKey"
					],
					"services": [
						"ArbitraryService",
						"SomeOtherService"
					]
				}
			}
		}
	}
}
```
Here "ArbitraryContentProvisionerKey" is a key, which is used just to identify content provisioner. It is used mostly for logging.
"ManifestsKey" is a key which will help to recognize manifests which should be processed by this specific content provisioner.


### Register custom content to import

By default, custom manifest file, which will be processed by custom content provisioner, must be registered such way:
```json
{
	"attributes": {
		"MWStakeContentProvisioner": {
			"ContentManifests": {
				"ManifestsKey": [
					"extensions/SomeExtension/path1/to/manifest1.json",
					"extensions/SomeExtension/path2/to/manifest2.json"
				]
			}
		}
	}
}
```
Here "ManifestsKey" must be the same value which was passed to that content provisioner as first argument.