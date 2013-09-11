<?php

class JR_CleverCms_Model_Cms_Page extends Mage_Cms_Model_Page
{
    public function move($parentId, $afterPageId)
    {
        /**
         * Validate new parent page id. (page model is used for backward
         * compatibility in event params)
         */
        $parent = Mage::getModel('cms/page')
            ->setStores(array($this->getStoreId()))
            ->load($parentId);

        if (!$parent->getId()) {
            Mage::throwException(
                Mage::helper('cms')->__('Page move operation is not possible: the new parent page was not found.')
            );
        }

        $eventParams = array(
            $this->_eventObject => $this,
            'parent'         => $parent,
            'page_id'        => $this->getId(),
            'prev_parent_id' => $this->getParentId(),
            'parent_id'      => $parentId
        );
        $moveComplete = false;

        $this->_getResource()->beginTransaction();
        try {
            Mage::dispatchEvent('cms_page_tree_move_before', $eventParams);
            Mage::dispatchEvent($this->_eventPrefix.'_move_before', $eventParams);

            $this->getResource()->changeParent($this, $parent, $afterPageId);

            Mage::dispatchEvent($this->_eventPrefix.'_move_after', $eventParams);
            Mage::dispatchEvent('cms_page_tree_move_after', $eventParams);
            $this->_getResource()->commit();

            $moveComplete = true;
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        if ($moveComplete) {
            Mage::dispatchEvent('page_move', $eventParams);
        }

        return $this;
    }

    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }

    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }

        return $ids;
    }

    public function getParentPage()
    {
        if (!$this->hasData('parent_page')) {
            $this->setData('parent_page', Mage::getModel('cms/page')->load($this->getParentId()));
        }

        return $this->_getData('parent_page');
    }

    public function formatUrlKey($str)
    {
        $str = Mage::helper('core')->removeAccents($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    public function loadRootByStoreId($storeId)
    {
        $rootId = $this->_getResource()->getStoreRootId($storeId);
        if ($rootId) {
            $this->load($rootId);
        }

        return $this;
    }

    public function getChildren()
    {
        return $this->getCollection()->addChildrenFilter($this);
    }

    public function getUrl()
    {
        return Mage::getBaseUrl() . $this->getIdentifier();
    }

    public function isRoot()
    {
        return 0 === (int) $this->getParentId();
    }

    public static function createDefaultStoreRootPage($storeId, $data = array())
    {
        $newRoot = Mage::getModel('cms/page')->setData(array(
            'title'         => Mage::helper('cms')->__('Home'),
            'root_template' => 'two_columns_right',
            'store_id'      => $storeId,
            'parent_id'     => 0,
            'level'         => 1,
        ))
        ->addData($data) // will override default data
        ->setCreateDefaultPermission(true)
        ->save();

        return $newRoot;
    }

    protected function _beforeDelete()
    {
        parent::_beforeDelete();
        $this->getResource()->decreaseChildrenCount($this, $this->getParentIds());

        return $this;
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();
        $this->getResource()->deleteChildren($this);

        return $this;
    }

    protected function _beforeSave()
    {
        if ($this->isRoot()) {
            $this->setIdentifier('');
        } else {
            if ($this->getPageId()) {
                // Edit existant page
                $identifiers = explode('/', $this->getIdentifier());
                array_pop($identifiers);
            } else {
                // Add new page
                $parent = $this->getParentPage();
                $identifiers = explode('/', $parent->getIdentifier());
                $this->getResource()->increaseChildrenCount($this, $parent->getPathIds());
                $this->setCreateDefaultPermission(true);
            }
            $identifier = $this->getUrlKey() ? $this->getUrlKey() : $this->getTitle();
            array_push($identifiers, $this->formatUrlKey($identifier));
            $this->setIdentifier(trim(implode('/', $identifiers), '/'));
        }

        parent::_beforeSave();

        $oldPage = Mage::getModel('cms/page')->load($this->getPageId()); // old page data

        if (!$this->isRoot() && Mage::helper('cms/page')->isCreatePermanentRedirects($this->getStoreId())) {
            // 301 Redirects
            $this->getResource()->updatePermanentRedirects($oldPage, $this);
        }

        $this->getResource()->updateChildrenIdentifiers($oldPage, $this->getIdentifier());

        return $this;
    }

    protected function _afterSave()
    {
        if (! $this->getPath() && $this->getPageId()) {
            if (! $this->getPosition()) {
                $this->setPosition($this->getPageId());
            }
            $path = '';
            if ($this->getParentId()) {
                $parent = $this->getParentPage();
                $path = $parent->getPath();
            }
            $path .= '/' . $this->getPageId();
            $path = trim($path, '/');
            $this->setPath($path)
                ->setLevel(count(explode('/', $path)))
                ->save();
        }

        if ($this->getCreateDefaultPermission()) {
            $storeId = $this->getStoreId();
            $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
            $resourceModel = Mage::getResourceModel('cms/page_permission');
            if ($this->isRoot() || $resourceModel->exists($storeId, $customerGroupId, $this->getParentId())) {
                Mage::getModel('cms/page_permission')->addData(array(
                    'store_id' => $storeId,
                    'customer_group_id' => $customerGroupId,
                    'page_id' => $this->getPageId(),
                ))
                ->save();
            }
            $this->setCreateDefaultPermission(false);
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $identifiers = explode('/', $this->getIdentifier());
        $this->setUrlKey(array_pop($identifiers));

        return $this;
    }
}
