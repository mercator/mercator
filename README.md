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


Features
--------

All the standard features of Magento Community Edition, plus the following extensions:

* [Colin Mollenhour's Redis cache backend](https://github.com/colinmollenhour/Cm_Cache_Backend_Redis) (BSD-3-Clause)
* [Enhanced Admin Product Grid](https://github.com/jayelkaake/enhancedgrid) (OSL-3.0)
* [Pulsestorm Launcher](https://github.com/astorm/PulsestormLauncher) (MIT)
* [AOE Scheduler](https://github.com/fbrnc/Aoe_Scheduler) (OSL-3.0)
* [Flagbit Change Attribute Set](https://github.com/Flagbit/Magento-ChangeAttributeSet) (GPL)
* [Admin Custom Shipping Rates](http://www.magentocommerce.com/magento-connect/admin-custom-shipping-rate.html) (OSL-3.0) 
* [Fontis Blog](https://github.com/fontis/fontis_blog) (OSL-3.0)

Where necessary, extensions are modified to allow them to be integrated into Mercator.

In addition, the following changes have been made to the core:

* Use of a public/ directory for the webroot, as per the approach outlined in [this blog post](http://www.fontis.com.au/blog/magento/move-magento-private-files-outside-docroot).
* Added Australian states into the core region list.
* Improved error report stack traces to show full strings.


Why another fork?
-----------------

The goals of the project are substantially different from the other prominent forks out there (e.g. Mage+) and thus requires a new codebase. We are not attempting to rigidly maintain backwards compatibility, so will make breaking changes every now and then.


Why "mercator"?
---------------

The name comes from [the latin word](http://en.wiktionary.org/wiki/mercator) for a merchant or trader and seemed appropriate for a software solution that allows the buying and selling or goods online.

It's _not_ named after the famous cartographer [Gerardus Mercator](http://en.wikipedia.org/wiki/Gerardus_Mercator) who gave us the Mercator projection world map, or the [Slovenian supermarket chain](http://en.wikipedia.org/wiki/Mercator_%28retail%29) (though they're both pretty cool too).


Audience
--------

Mercator is intended for web developers familiar with Magento, and it is _not_ a primary goal of this project to make a system that is easy to install or modify for those who are not already comfortable working with Magento.


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
