<?php
/**
 * @method int getRootId()
 * @method JR_CleverCms_Block_Cms_Navigation setRootId(int $rootId)
 */
class JR_CleverCms_Block_Cms_Navigation
    extends JR_CleverCms_Block_Catalog_Navigation
    implements Mage_Widget_Block_Interface
{

    function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }

    function _construct()
    {
        /*
         * In case template was passed through constructor
         * we assign it to block's property _template
         * Mainly for those cases when block created
         * not via Mage_Core_Model_Layout::addBlock()
         */
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }

	    $this->getDataSetDefault('max_level', 1000);
    }

    public function getCacheKeyInfo()
    {
	    return array(
            'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate()
        );
    }

    /**
     * @return JR_CleverCms_Model_Cms_Page
     */
    public function getRoot()
    {
        if ($this->getRootId())
        {
            return Mage::getModel('cms/page')->load($this->getRootId());
        } elseif ($page = $this->getCurrentCmsPage())
        {
            $pathIds = $page->getPathIds();

            /** @var $rootPage JR_CleverCms_Model_Cms_Page */
            $rootPage = Mage::getModel('cms/page')->load($pathIds[$this->getDataSetDefault('root_depth', 1)]);
            return $rootPage;
        } else {
            return null;
        }
    }


    /**
     * @return string
     */
    public function getPages()
    {
        $html = '';
        if ($root = $this->getRoot())
        {
            $children = $root->getChildren();
            if (Mage::helper('cms/page')->isPermissionsEnabled($this->getStore())) {
                $children->addPermissionsFilter($this->getCustomerGroupId());
            }

            foreach ($children as $child)
            {
                /** @var $child JR_CleverCms_Model_Cms_Page */
                if ($child->getIsActive() && $child->getLevel() <= ($this->getMaxLevel() + 1))
                {
                    $html .= $this->_renderCmsMenuItemHtml($child);
                }
            }
        }

	    return $html;
    }


    /**
     * Render CMS to html
     *
     * @param Mage_Cms_Model_Page $page
     * @param int                 $level              Nesting level number
     * @param boolean             $isLast             Whether or not this item is last, affects list item class
     * @param boolean             $isFirst            Whether or not this item is first, affects list item class
     * @param boolean             $isOutermost        Whether or not this item is outermost, affects list item class
     * @param string              $outermostItemClass Extra class of outermost list items
     * @param string              $childrenWrapClass  If specified wraps children list in div with this class
     * @param bool                $noEventAttributes  Whether or not to add on* attributes to list item
     *
     * @return string
     */
    protected function _renderCmsMenuItemHtml($page, $level = 0, $isLast = false, $isFirst = false,
	    $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {
        if (! $this->_isAllowed($page)) {
            return '';
        }
        $html = array();

        // get all children
        $children = $page->getChildren();
        if (Mage::helper('cms/page')->isPermissionsEnabled($this->getStore())) {
            $children->addPermissionsFilter($this->getCustomerGroupId());
        }
        $childrenCount = $children->count();
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
            $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        // prepare list item html classes
        $classes = array();
        $classes[] = 'level'.$level;
        // note: not dealing with the 'nav-' class at the moment
        if ($this->isCmsPageActive($page)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="'.$outermostItemClass.'"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }

        // assemble list item with attributes
        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html[] = $htmlLi;
        $html[] .= '<a href="'. $page->getUrl() . "/" .'"'.$linkClass.'>';
        $html[] .= '<span>'. $this->escapeHtml($page->getTitle()) .'</span>';
        $html[] .= '</a>';

        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            if ($child->getLevel() <= ($this->getMaxLevel() + 1))
            {
                $htmlChildren .= $this->_renderCmsMenuItemHtml(
                    $child,
                    ($level + 1),
                    ($j == $activeChildrenCount - 1),  // is last
                    ($j == 0),                         // is first
                    false,                             // is outermost
                    $outermostItemClass,
                    $childrenWrapClass,
                    $noEventAttributes
                );
                $j++;
            }
        }

        if (!empty($htmlChildren)) {
            if ($childrenWrapClass) {
            $html[] = '<div class="' . $childrenWrapClass . '">';
            }
            $html[] = '<ul class="level'. $level .'">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
            if ($childrenWrapClass) {
            $html[] = '</div>';
            }
        }

        $html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }
}