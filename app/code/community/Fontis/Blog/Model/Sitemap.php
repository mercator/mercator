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

require_once "Mage/Sitemap/Model/Sitemap.php";

class Fontis_Blog_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    public function generateXml()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate("Y-m-d");
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/category/changefreq");
        $priority   = (string) Mage::getStoreConfig("sitemap/category/priority");
        $collection = Mage::getResourceModel("sitemap/catalog_category")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/product/changefreq");
        $priority   = (string) Mage::getStoreConfig("sitemap/product/priority");
        $collection = Mage::getResourceModel("sitemap/catalog_product")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig("sitemap/page/changefreq");
        $priority   = (string)Mage::getStoreConfig("sitemap/page/priority");
        $collection = Mage::getResourceModel("sitemap/cms_page")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate blog post pages sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/blog/changefreq_post");
        $priority   = (string) Mage::getStoreConfig("sitemap/blog/priority_post");
        $collection = Mage::getModel("blog/post")->getCollection($storeId);
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);
        $route = Mage::helper("blog")->getBlogRoute();
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $route . "/" . $item->getIdentifier()),
                substr($item->getUpdateTime(), 0, 10),
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate blog category pages sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/blog/changefreq_cat");
        $priority   = (string) Mage::getStoreConfig("sitemap/blog/priority_cat");
        $collection = Mage::getModel("blog/cat")->getCollection($storeId);
        $route = Mage::helper("blog")->getBlogRoute();
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $route . "/cat/" . $item->getIdentifier()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton("core/date")->gmtDate("Y-m-d H:i:s"));
        $this->save();

        return $this;
    }
}
