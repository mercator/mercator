<?php

class JR_CleverCms_Model_Resource_Cms_Page_Tree extends Varien_Data_Tree_Dbp
{
    protected $_collection;

    protected $_storeId = null;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        parent::__construct(
            $resource->getConnection('write'),
            $resource->getTableName('cms_page_tree'),
            array(
                Varien_Data_Tree_Dbp::ID_FIELD       => 'page_id',
                Varien_Data_Tree_Dbp::PATH_FIELD     => 'path',
                Varien_Data_Tree_Dbp::ORDER_FIELD    => 'position',
                Varien_Data_Tree_Dbp::LEVEL_FIELD    => 'level',
            )
        );
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = (int) $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    public function addCollectionData($collection=null, $sorted=false, $exclude=array(), $toLoad=true, $onlyActive = false)
    {
        if (is_null($collection)) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {

            $disabledIds = $this->_getDisabledIds($collection);
            if ($disabledIds) {
                $collection->addFieldToFilter('page_id', array('nin'=>$disabledIds));
            }
        }

        if($toLoad) {
            $collection->load();

            foreach ($collection as $page) {
                if ($this->getNodeById($page->getId())) {
                    $this->getNodeById($page->getId())
                        ->addData($page->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }

    public function getCollection($sorted=false)
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    public function setCollection($collection)
    {
        if (!is_null($this->_collection)) {
            destruct($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    protected function _getDefaultCollection($sorted = false)
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        /* @var $collection JR_CleverCms_Model_Resource_Cms_Page_Collection */

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }

        return $collection;
    }

    public function toSelectHtml($name = '', $value = false, $id = '')
    {
        $html = '<select name="' . $name . '"' . ($id ? ' id="' . $id . '"' : '')  . '>';
        $html .= '<option value="">' . Mage::helper('cms')->__('Select Page...') . '</option>';
        $currentStoreId = null;
        foreach ($this->getNodes() as $node) {
            if ($node->getStoreId() != $currentStoreId) {
                $store = Mage::app()->getStore($node->getStoreId());
                $html .= '<optgroup label="'. $store->getWebsite()->getName() .'"></optgroup>';
                $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;'. $store->getName() .'">';
                $currentStoreId = $node->getStoreId();
                if (null !== $currentStoreId) {
                    $html .= '</optgroup>';
                }
            }
            $selected = ($node->getPageId() == $value) ? 'selected="selected"' : '';
            $html .= '<option value="'. $node->getPageId() .'" '. $selected  .'>'. str_repeat('&nbsp;&nbsp;&nbsp;', $node->getLevel() + 1) . $node->getTitle() . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}