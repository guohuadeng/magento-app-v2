<?php
/**
 * Sunpop Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sunpop License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   Sunpop
 * @package    Sunpop_News
 * @copyright  Copyright (c) 2012 Sunpop Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

try{
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('clnews/news')};
CREATE TABLE {$this->getTable('clnews/news')} (
    `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '',
    `url_key` varchar(255) NOT NULL DEFAULT '',
    `short_content` text NOT NULL,
    `full_content` text NOT NULL,
    `document` varchar(255) NOT NULL DEFAULT '',
    `image_short_content` varchar(255) NOT NULL DEFAULT '',
    `image_full_content` varchar(255) NOT NULL DEFAULT '',
    `image_short_content_show` int(11) NOT NULL DEFAULT '0',
    `image_full_content_show` int(11) NOT NULL DEFAULT '0',
    `full_path_document` varchar(255) NOT NULL DEFAULT '',
    `status` smallint(6) NOT NULL DEFAULT '0',
    `news_time` datetime DEFAULT NULL,
    `created_time` datetime DEFAULT NULL,
    `update_time` datetime DEFAULT NULL,
    `publicate_from_time` datetime DEFAULT NULL,
    `publicate_to_time` datetime DEFAULT NULL,
    `author` varchar(255) NOT NULL DEFAULT '',
    `meta_keywords` text NOT NULL,
    `meta_description` text NOT NULL,
    `comments_enabled` tinyint(11) NOT NULL,
    `publicate_from_hours` int(11) NOT NULL DEFAULT '0',
    `publicate_to_hours` int(11) NOT NULL DEFAULT '0',
    `publicate_from_minutes` int(11) NOT NULL DEFAULT '0',
    `publicate_to_minutes` int(11) NOT NULL DEFAULT '0',
    `link` varchar(255) NOT NULL DEFAULT '',
    `tags` text NOT NULL,
    `short_height_resize` int(11) DEFAULT NULL,
    `short_width_resize` int(11) DEFAULT NULL,
    `full_width_resize` int(11) DEFAULT NULL,
    `full_height_resize` int(11) DEFAULT NULL,
    PRIMARY KEY ( `news_id` )
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO {$this->getTable('clnews/news')} VALUES (NULL, 'Welcome to Sunpop news', 'test', 'This is a short content', 'This is a content', '', '', '', '0', '0', '', '1', NOW( ), NOW( ), NOW( ), NULL, NULL, 'Test author', 'Meta Keywords', 'Meta Description', '0', '0', '0', '0', '0', '', '', NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS {$this->getTable('clnews/comment')};
CREATE TABLE {$this->getTable('clnews/comment')} (
    `comment_id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
    `news_id` smallint( 11 ) NOT NULL default '0',
    `comment` text NOT NULL ,
    `comment_status` smallint( 6 ) NOT NULL default '0',
    `created_time` datetime default NULL ,
    `user` varchar( 255 ) NOT NULL default '',
    `email` varchar( 255 ) NOT NULL default '',
    PRIMARY KEY ( `comment_id` )
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO {$this->getTable('clnews/comment')} (`comment_id` ,`news_id` ,`comment` ,`comment_status` ,`created_time` ,`user` ,`email`)
VALUES (NULL , '1', 'This is the first comment. It can be edited, deleted or set to unapproved so it is not displayed. This can be done in the admin panel.', '2', NOW( ) , '', '');

DROP TABLE IF EXISTS {$this->getTable('clnews/category')};
CREATE TABLE {$this->getTable('clnews/category')} (
    `category_id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
    `title` varchar( 255 ) NOT NULL default '',
    `url_key` varchar( 255 ) NOT NULL default '',
    `sort_order` tinyint ( 6 ) NOT NULL ,
    `meta_keywords` text NOT NULL ,
    `meta_description` text NOT NULL ,
PRIMARY KEY ( `category_id` )
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO {$this->getTable('clnews/category')} (
    `category_id` ,
    `title`,
    `url_key`
)
VALUES (
NULL , 'Default', 'default'
);

DROP TABLE IF EXISTS {$this->getTable('clnews/news_store')};
CREATE TABLE {$this->getTable('clnews/news_store')} (
    `news_id` smallint(6) unsigned,
    `store_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('clnews/category_store')};
CREATE TABLE {$this->getTable('clnews/category_store')} (
    `category_id` smallint(6) unsigned ,
    `store_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS {$this->getTable('clnews/news_category')};
CREATE TABLE {$this->getTable('clnews/news_category')} (
    `category_id` smallint(6) unsigned ,
    `news_id` smallint(6) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");

}catch(Exception $e){}

$installer->endSetup();
