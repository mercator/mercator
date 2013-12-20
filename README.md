Mercator
========

Overview
--------

Mercator is a community driven distribution of Magento. It leverages the best open source developer contributions to produce an integrated fork that expands upon the feature set provided by the upstream Community Edition.

The project has the following goals:

* Include the best community extensions to add the most commonly required features, tested and already made to work together.
* Provide a better platform for new projects that does *not* prioritise backwards compatibility with official Magento releases. This allows core changes and fixes where appropriate.
* Present an alternative to Enterprise Edition, with a similar feature set but under an open source licensing and development model.
* Continue support and development of the Magento 1.x codebase.

Installation
------------

1. Clone the [Mercator](https://github.com/mercator/mercator) repository using git.
1. Download and install [Composer](http://getcomposer.org/download/).
1. From inside the cloned Mercator directory, use Composer to download and symlink extensions. If you added Composer to your path, you can run: `composer.phar install`

Features
--------

All the standard features of Magento Community Edition, plus the following extensions:

* [Admin Custom Shipping Rates](http://www.magentocommerce.com/magento-connect/admin-custom-shipping-rate.html) (OSL-3.0)
* [AOE Scheduler](https://github.com/fbrnc/Aoe_Scheduler) (OSL-3.0)
* [BL Enhanced Admin Grids](https://github.com/mage-eag/mage-enhanced-admin-grids) (OSL 3.0)
* [Catalin SEO Ajax Layered Nav](http://www.magentocommerce.com/magento-connect/layered-navigation-seo-6101.html) (OSL-3.0)
* [Colin Mollenhour's Redis cache backend](https://github.com/colinmollenhour/Cm_Cache_Backend_Redis) (New BSD)
* [Colin Mollenhour's Redis session handler](https://github.com/colinmollenhour/Cm_RedisSession) (New BSD)
* [Flagbit Change Attribute Set](https://github.com/Flagbit/Magento-ChangeAttributeSet) (GPL)
* [Fontis Blog](https://github.com/fontis/fontis_blog) (OSL-3.0)
* [Fontis reCAPTCHA](https://github.com/fontis/fontis_recaptcha) (OSL-3.0)
* [Indust Admin Custom Shipping Rates](http://www.magentocommerce.com/magento-connect/admin-custom-shipping-rate.html) (OSL-3.0)
* H&O's fork of [JR CleverCMS](https://github.com/ho-nl/magento-clever-cms) (GPL)
* [Netresearch Product Visibility](https://github.com/netresearch/Magento-Productvisibility) (OSL-3.0)
* [Netzarbeiter Customer Activation](https://github.com/Vinai/customer-activation) (OSL 3.0)
* [Netzarbeiter Groups Catalog 2](https://github.com/Vinai/groupscatalog2) (OSL 3.0)
* [Offi Customer Attributes Manager](http://www.magentocommerce.com/magento-connect/customer-attributes-manager-5092.html) (OSL-3.0)
* [Pulsestorm Launcher](https://github.com/astorm/PulsestormLauncher) (MIT)
* [System Configuration Search](https://github.com/astorm/SystemConfigurationSearch) (MIT)
* [Unirgy Gift Certificates](http://www.unirgy.com/products/ugiftcert/) (OSL-3.0)

Where necessary, extensions are modified to allow them to be integrated into Mercator.

In addition, the following changes have been made to the core:

* Use of a public/ directory for the webroot, as per the approach outlined in [this blog post](http://www.fontis.com.au/blog/magento/move-magento-private-files-outside-docroot).
* Added Australian states into the core region list.
* Improved error report stack traces to show full strings.
* Removed Magento Connect Manager from admin panel (it's still available on the command line).
* Integrated the [Yoast MetaRobots](http://www.magentocommerce.com/magento-connect/yoast-metarobots.html) (OSL-3.0) extension.


Why another fork?
-----------------

The goals of the project are substantially different from the other prominent forks out there (e.g. Mage+) and thus requires a new codebase. We are not attempting to rigidly maintain backwards compatibility, so will make breaking changes every now and then.


Why "mercator"?
---------------

The name comes from [the latin word](http://en.wiktionary.org/wiki/mercator) for a merchant or trader and seemed appropriate for a software solution that allows the buying and selling of goods online.

It's *not* named after the famous cartographer [Gerardus Mercator](http://en.wikipedia.org/wiki/Gerardus_Mercator) who gave us the Mercator projection world map, or the [Slovenian supermarket chain](http://en.wikipedia.org/wiki/Mercator_%28retail%29) (though they're both pretty cool too).


Audience
--------

Mercator is intended for web developers familiar with Magento, and it is *not* a primary goal of this project to make a system that is easy to install or modify for those who are not already comfortable working with Magento.


Installation
------------

Mercator is installed much like standard versions of Magento. The main change to be aware of is that public files (i.e. index.php, media, skin) have been moved to a 'public' subdirectory under the Magento root. This protects private files (under app, lib, etc) from being accidentally exposed. The public subdirectory should be used as the document root in your web server configuration. Basic example Apache 2 and Nginx configuration files have been provided (mercator.apache and mercator.nginx).

When installing other extensions, bear in mind that public files now go in the 'public' subdirectory. Standard Magento extension packages will place their publicly accessible files in their standard locations under the Magento root, rather than using Mercator's modfified layout. You may need to move any media or skin files (or other public files) from under the Magento root to the corresponding location in the public subdirectory. Mercator has been modified to use the new paths internally, so code changes should not usually be necessary if the extension has been written correctly.

Further information about how to configure popular web servers to run Mercator can be found in the [installation instructions](https://github.com/fontis/mercator/wiki/Installing-Mercator).


Reporting Issues
----------------

As Mercator is largely a distribution which includes other extensions, we ask that any bug reports are lodged with the original extension authors so that everyone can benefit.

Issues which are specifically related to functionality which has been changed in Mercator from Magento core, or arising due to changes made when integrating an extension into Mercator, can be submitted as a new issue on [the GitHub issues page](https://github.com/fontis/mercator/issues).


Contributing
------------

As one of the goals of this project is to incorporate the best quality contributions from around the Magento development community, we invite other extension developers to submit open source extensions for inclusion where they address common requirements and are aligned with the project goals. We aren't looking to add extensions that are purely developer-centric tools, such as [ModuleCreator](http://www.magentocommerce.com/magento-connect/modulecreator.html) or [Developer Tools](https://github.com/DoghouseMedia/Dhmedia_Devel--Magento-Developer-Tools-). Contributions which allow easier debugging of problems or are core changes needed for other tools to function would be exceptions to this rule.

Any contribution must be under an appropriate open source license, such as the OSL 3.0, MIT, BSD or Apache licenses.


Disclaimer
----------

Magento is a trademark of Magento Inc., an eBay Inc. company, registered in the U.S. and other countries.
