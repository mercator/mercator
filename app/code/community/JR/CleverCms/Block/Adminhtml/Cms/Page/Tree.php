<?php

class JR_CleverCms_Block_Adminhtml_Cms_Page_Tree extends Mage_Adminhtml_Block_Template
{
    protected $_withChildrenCount;

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->_withChildrenCount = true;
    }

    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/add", array(
            '_current'=>true,
            'id'=>null,
            '_query' => false
        ));

        $this->setChild('add_sub_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('cms')->__('Add Page'),
                    'onclick'   => "addNew('".$addUrl."', false)",
                    'class'     => 'add',
                    'id'        => 'add_subpage_button',
                    'style'     => $this->canAddSubPage() ? '' : 'display: none;'
                ))
        );

        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setSwitchUrl($this->getUrl('*/*/switch', array('_current'=>true, '_query'=>false, 'store'=>null)))
                ->setTemplate('cms/page/store/switcher.phtml')
        );
        return parent::_prepareLayout();
    }

    protected function _getDefaultStoreId()
    {
        return 0;
    }

    public function getPageCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('page_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('cms/page')->getCollection();

            /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
            $collection->addStoreFilter($storeId);

            $this->setData('page_collection', $collection);
        }
        return $collection;
    }

    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    public function getLoadTreeUrl($expanded=null)
    {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if (
            (is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/pagesJson', $params);
    }

    public function getNodesUrl()
    {
        return $this->getUrl('*/cms_page/jsonTree');
    }

    public function getSwitchTreeUrl()
    {
        return $this->getUrl("*/cms_page/tree", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    public function getIsWasExpanded()
    {
        return Mage::getSingleton('admin/session')->getIsTreeWasExpanded();
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/cms_page/move', array('store'=>$this->getRequest()->getParam('store')));
    }

    public function getTree($parenNodePage=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodePage));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : array();
        return $tree;
    }

    public function getTreeJson($parenNodePage=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodePage));
        $json = Mage::helper('core')->jsonEncode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $pages = Mage::getResourceSingleton('cms/page_tree')
            ->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($path);
        if (empty($pages)) {
            return '';
        }
        foreach ($pages as $key => $page) {
            $pages[$key] = $this->_getNodeJson($page);
        }
        return
            '<script type="text/javascript">'
            . $javascriptVarName . ' = ' . Mage::helper('core')->jsonEncode($pages) . ';'
            . ($this->canAddSubPage() ? '$("add_subpage_button").show();' : '$("add_subpage_button").hide();')
            . '</script>';
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Varien_Data_Tree_Node|array $node
     * @param int $level
     * @return string
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByPageIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isPageMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and page is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = $this->_isParentSelectedPage($node);

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get page name
     *
     * @param Varien_Object $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->htmlEscape($node->getTitle());
        if ($this->_withChildrenCount) {
             $result .= ' (' . $node->getChildrenCount() . ')';
        }
        return $result;
    }

    protected function _isPageMoveable($node)
    {
        $options = new Varien_Object(array(
            'is_moveable' => true,
            'page' => $node
        ));

        Mage::dispatchEvent('adminhtml_page_tree_is_moveable',
            array('options'=>$options)
        );

        return $options->getIsMoveable();
    }

    protected function _isParentSelectedPage($node)
    {
        if ($node && $this->getPage()) {
            $pathIds = $this->getPage()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    public function canAddRootPage()
    {
        return false;
    }

    public function canAddSubPage()
    {
        $options = new Varien_Object(array('is_allow'=>true));
        Mage::dispatchEvent(
            'adminhtml_page_tree_can_add_sub_page',
            array(
                'page'    => $this->getPage(),
                'options' => $options,
                'store'   => $this->getStore()->getId()
            )
        );

        return $options->getIsAllow();
    }

    public function getPage()
    {
        return Mage::registry('cms_page');
    }

    public function getPageId()
    {
        if ($this->getPage()) {
            return $this->getPage()->getId();
        }
        return Mage::getResourceModel('cms/page')->getStoreRootId($this->getStoreId());
    }

    public function getPageName()
    {
        return $this->getPage()->getName();
    }

    public function getPagePath()
    {
        if ($this->getPage()) {
            return $this->getPage()->getPath();
        }
        return '';
    }

    public function hasStoreRootPage()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return Mage::app()->getStore($storeId);
    }

    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    public function getRoot($parentNodePage = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodePage) && $parentNodePage->getId()) {
            return $this->getNode($parentNodePage, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');
            $rootId = Mage::getResourceModel('cms/page')->getStoreRootId($storeId);
            if (! $rootId) {
                $newRoot = JR_CleverCms_Model_Cms_Page::createDefaultStoreRootPage($storeId);
                $rootId = $newRoot->getId();
            }

            $tree = Mage::getResourceSingleton('cms/page_tree')
                ->load(null, $recursionLevel);

            if ($this->getPage()) {
                $tree->loadEnsuredNodes($this->getPage(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getPageCollection());
            $root = $tree->getNodeById($rootId);

            if (!$root) {
                Mage::throwException('Could not retrieve root page of store ' . $storeId);
            }

            $root->setIsVisible(true);
            $root->setName($root->getTitle());
            if ($this->_withChildrenCount) {
                $root->setName($root->getName() . ' (' . $root->getChildrenCount() . ')');
            }

            Mage::register('root', $root);
        }

        return $root;
    }

    public function getRootByIds($ids)
    {
        $root = Mage::registry('root');
        if (null === $root) {
            $storeId = (int) $this->getRequest()->getParam('store');
            $pageTreeResource = Mage::getResourceSingleton('cms/page_tree');
            $ids    = $pageTreeResource->getExistingPageIdsBySpecifiedIds($ids);
            $tree   = $pageTreeResource->loadByIds($ids);
            $rootId = Mage::getResourceModel('cms/page')->getStoreRootId($storeId);
            if (! $rootId) {
                $newRoot = $this->_createStoreRootPage($storeId);
                $rootId = $newRoot->getId();
            }
            $root   = $tree->getNodeById($rootId);

            if (!$root) {
                Mage::throwException('Could not retrieve root page of store ' . $storeId);
            }

            $tree->addCollectionData($this->getPageCollection());
            Mage::register('root', $root);
        }
        return $root;
    }

    public function getNode($parentNodePage, $recursionLevel=2)
    {
        $tree = Mage::getResourceModel('cms/page_tree');

        $nodeId     = $parentNodePage->getId();
        $parentId   = $parentNodePage->getParentId();

        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        $tree->addCollectionData($this->getPageCollection());

        return $node;
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl("*/cms_page/edit", array('_current'=>true, 'store'=>$this->getRequest()->getParam('store'), '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach (Mage::app()->getGroups() as $store) {
                $ids[] = $store->getRootPageId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}
