<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2013 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('blog/blog')};
CREATE TABLE {$this->getTable('blog/blog')} (
    `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `cat_id` smallint(11) NOT NULL default '0',
    `title` varchar(255) NOT NULL default '',
    `post_content` text NOT NULL,
    `summary_content` text NOT NULL,
    `status` smallint(6) NOT NULL default '0',
    `created_time` datetime default NULL,
    `update_time` datetime default NULL,
    `identifier` varchar(255) NOT NULL default '',
    `user` varchar(255) NOT NULL default '',
    `update_user` varchar(255) NOT NULL default '',
    `meta_keywords` text NOT NULL,
    `meta_description` text NOT NULL,
    `comments` TINYINT(11) NOT NULL,
    PRIMARY KEY (`post_id`),
    UNIQUE KEY `identifier` (`identifier`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/comment')};
CREATE TABLE {$this->getTable('blog/comment')} (
    `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `post_id` smallint(11) NOT NULL default '0',
    `comment` text NOT NULL,
    `status` smallint(6) NOT NULL default '0',
    `created_time` datetime default NULL,
    `user` varchar(255) NOT NULL default '',
    `email` varchar(255) NOT NULL default '',
    `in_reply_to` int(11) unsigned NULL default NULL,
    PRIMARY KEY (`comment_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/cat')};
CREATE TABLE {$this->getTable('blog/cat')} (
    `cat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL default '',
    `identifier` varchar(255) NOT NULL default '',
    `sort_order` tinyint (6) NOT NULL,
    `meta_keywords` text NOT NULL,
    `meta_description` text NOT NULL,
    PRIMARY KEY (`cat_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/store')};
CREATE TABLE {$this->getTable('blog/store')} (
    `post_id` smallint(6) unsigned,
    `store_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/cat_store')};
CREATE TABLE {$this->getTable('blog/cat_store')} (
    `cat_id` smallint(6) unsigned,
    `store_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/post_cat')};
CREATE TABLE {$this->getTable('blog/post_cat')} (
    `cat_id` smallint(6) unsigned,
    `post_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");

$installer->endSetup();
