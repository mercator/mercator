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

class Fontis_Blog_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();

        $blog = new Fontis_Blog_Controller_Router();
        $front->addRouter("blog", $blog);
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $route = Mage::helper("blog")->getBlogRoute();
        $identifier = $request->getPathInfo();

        if (substr(str_replace("/", "", $identifier), 0, strlen($route)) != $route) {
            return false;
        }

        $identifier = substr_replace($request->getPathInfo(),'', 0, strlen("/" . $route. "/") );
        $identifier = str_replace('.html', '', $identifier);
        if ($identifier == '') {
            $request->setModuleName("blog")
                ->setControllerName("index")
                ->setActionName("index");
            return true;
        }
        
        if (strpos($identifier, '/')) {
            $page = substr($identifier, strpos($identifier, '/') + 1);
        }
        
        if (substr($identifier, 0, strlen('cat/')) == 'cat/') {
            $identifier = substr_replace($identifier, '', 0, strlen('cat/'));

            if (strpos($identifier, '/page/')) {
                $page = substr($identifier, strpos($identifier, '/page/') + 6);
                $identifier = substr_replace($identifier, '', strpos($identifier, '/page/'), strlen($page) + 6);
            }
            
            $rss = false;
            if (strpos($identifier, '/rss')) {
                $rss = true;
                $identifier = substr_replace($identifier, '', strpos($identifier, '/rss'), strlen($page) + 4);
            }
            $identifier = str_replace('/', '', $identifier);
            
            $cat = Mage::getSingleton('blog/cat');
            if (!$cat->load($identifier)->getCatId()) {
                return false;
            }
            
            if ($rss) {
                $request->setModuleName('blog')
                    ->setControllerName('rss')
                    ->setActionName('index')
                    ->setParam('identifier', $identifier);
            } else {
                $request->setModuleName('blog')
                    ->setControllerName('cat')
                    ->setActionName('view')
                    ->setParam('identifier', $identifier);
                if (isset($page)) {
                    $request->setParam('page', $page);
                }
            }
            return true;
        } else if (substr($identifier, 0, strlen('page/')) == 'page/') {
            $identifier = substr_replace($identifier, '', 0, strlen('page/'));
            
            $request->setModuleName('blog')
                ->setControllerName('index')
                ->setActionName('index');
            if (isset($page)) {
                $request->setParam('page', $page);
            }
            return true;
        } else if (substr($identifier, 0, strlen('rss')) == 'rss') {
            $identifier = substr_replace($identifier, '', 0, strlen('rss/'));
            if ($identifier) {
                // Stops the user from putting random strings on the end of the URL
                return false;
            }

            $request->setModuleName('blog')
                ->setControllerName('rss')
                ->setActionName('index');
            return true;
        } else if (substr($identifier, 0, strlen("archive")) == "archive") {
            $identifier = substr_replace($identifier, '', 0, strlen("archive/"));
            if ($identifier) {
                $request->setModuleName("blog")
                    ->setControllerName("archive")
                    ->setActionName("view")
                    ->setParam("date", $identifier);
            } else {
                $request->setModuleName("blog")
                    ->setControllerName("archive")
                    ->setActionName("index");
            }
            return true;
        } else {
            $identifier = str_replace('/', '', $identifier);
            $post = Mage::getSingleton('blog/post');
            if (!$post->load($identifier)->getId()) {
                return false;
            }

            $request->setModuleName('blog')
                ->setControllerName('post')
                ->setActionName('view')
                ->setParam('identifier', $identifier);
            if (isset($page)) {
                $request->setParam('page', $page);
            }
            return true;
        }
        return false;
    }
}
