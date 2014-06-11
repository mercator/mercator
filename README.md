Mercator
========

Overview
--------

Mercator is a community driven distribution of Magento. It leverages the best open source developer contributions to
produce an integrated fork that expands upon the feature set provided by the upstream Community Edition.

The project has the following goals:

* Include the best community extensions to add the most commonly required features, tested and already made to work together.
* Provide a better platform for new projects that does *not* prioritise backwards compatibility with official Magento releases. This allows core changes and fixes where appropriate.
* Present an alternative to Enterprise Edition, with a similar feature set but under an open source licensing and development model.
* Continue support and development of the Magento 1.x codebase.


Quickstart
----------

A pre-built [Vagrant box](https://github.com/mercator/mercator/wiki/Vagrant) is available to get up and running with
Mercator in minutes. Full details can be found on the [wiki](https://github.com/mercator/mercator/wiki/Vagrant), but
for the impatient:

    wget http://dl.fontis.com.au/mercator.box
    vagrant box add --name mercator mercator.box
    vagrant init mercator
    vagrant up

Then point a browser at [http://127.0.0.1:8081](http://127.0.0.1:8081/).


Installation
------------

    git clone https://github.com/mercator/mercator
    cd mercator
    composer install

More detailed installation information, including sample configurations for popular web servers, can be found in the
[full installation instructions](https://github.com/mercator/mercator/wiki/Installing-Mercator).


Features
--------

All the standard features of Magento Community Edition, plus [a number of community extensions](https://github.com/mercator/mercator/wiki/Extensions).
Where necessary, extensions are modified to allow them to be integrated into Mercator.

Details of additional features and core changes can be found on the [Features page](https://github.com/mercator/mercator/wiki/Features).


Audience
--------

Mercator is intended for web developers familiar with Magento, and it is *not* a primary goal of this project to make a
system that is easy to install or modify for those who are not already comfortable working with Magento.


Contributing
------------

Given one of the goals of this project is to incorporate the best quality contributions from around the Magento development
community, we invite other extension developers to submit open source extensions for inclusion where they address common
requirements and are aligned with the project goals. We aren't looking to add extensions that are purely developer-centric
tools, such as [ModuleCreator](http://www.magentocommerce.com/magento-connect/modulecreator.html) or
[Developer Tools](https://github.com/DoghouseMedia/Dhmedia_Devel--Magento-Developer-Tools-).
Contributions which allow easier debugging of problems or are core changes needed for other tools to function would be
exceptions to this rule.

Any contribution must be under an appropriate open source license, such as the OSL 3.0, MIT, BSD or Apache licenses.


Reporting Issues
----------------

As Mercator is largely a distribution which includes other extensions, we ask that any bug reports are lodged with the
original extension authors so that everyone can benefit.

Issues which are specifically related to functionality which has been changed in Mercator from Magento core, or arising
due to changes made when integrating an extension into Mercator, can be submitted as a new issue on
[the GitHub issues page](https://github.com/mercator/mercator/issues).


Why another fork?
-----------------

The goals of the project are substantially different from the other prominent forks out there (e.g. Mage+) and thus
requires a new codebase. We are not attempting to rigidly maintain backwards compatibility, so may make breaking
changes every now and then if there is a compelling reason to do so.


Why "mercator"?
---------------

The name comes from [the latin word](http://en.wiktionary.org/wiki/mercator) for a merchant or trader and seemed
appropriate for a software solution that allows the buying and selling of goods online.

It's *not* named after the famous cartographer [Gerardus Mercator](http://en.wikipedia.org/wiki/Gerardus_Mercator) who
gave us the Mercator projection world map, or the [Slovenian supermarket chain](http://en.wikipedia.org/wiki/Mercator_%28retail%29)
(though they're both pretty cool too).


Disclaimer
----------

Magento is a trademark of Magento Inc., an eBay Inc. company, registered in the U.S. and other countries.