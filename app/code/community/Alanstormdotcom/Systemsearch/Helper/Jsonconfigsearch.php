<?php
/**
* Open Source Initiative OSI - The MIT License (MIT):Licensing
* 
* The MIT License (MIT)
* Copyright (c) 2009 - 2011 Pulse Storm LLC
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class Alanstormdotcom_Systemsearch_Helper_Jsonconfigsearch extends Mage_Core_Helper_Abstract
{
    public function prepareAndSendResponse($nodes, $terms)
    {
        $response = new stdClass();
        $response->responseText 	= array();
        $response->results			= array();
        $response->search_terms		= $terms;
        $helper 					= Mage::helper('adminhtml');
        $simpleXmlHelper            = $this->fetchSimpleXmlHelper();
        $adminModel                 = Mage::getModel("adminhtml/url");
        foreach ($nodes as $type=>$all_of_type) {
            foreach($all_of_type as $value) {
                $value = $this->fetchSimpleXmlHelper()->switchFromCommentToLabel($value);

                if ($type == Alanstormdotcom_Systemsearch_Model_Configsearch::TYPE_SYSTEM_CONFIG_SECTION) {
                    $result 				= new stdClass();
                    $result->found			= true;
                    $result->found_type		= 'nav';   //other option is 'group'
                    $result->found_label	= $helper->__((string) $value);
                    $result->type 			= $type;
                    $result->path 			= $simpleXmlHelper->getPathExpression($value);
                    $section = explode("/", $result->path);
                    $sectionUrl = $adminModel->getUrl("*/system_config/edit", array("section" => $section[3]));

                    $response->results[]	= $result;

                    $response->responseText[] = 'Configuration*/*<a href="' . $sectionUrl . '">' . (string) $value . '</a>';
                } else if ($type == Alanstormdotcom_Systemsearch_Model_Configsearch::TYPE_SYSTEM_CONFIG_GROUP) {
                    $result 				= new stdClass();
                    $result->found			= true;
                    $result->found_type		= 'group';   //other option is 'group'
                    $result->found_label	= $helper->__((string) $value);
                    $label_group			= $result->found_label;
                    $result->type 			= $type;
                    $result->path 			= $simpleXmlHelper->getPathExpression($value);
                    $path = explode("/", $result->path);
                    $sectionUrl = $adminModel->getUrl("*/system_config/edit", array("section" => $path[3]));
                    $groupUrl = $sectionUrl . "#" . $path[3] . "_" . $path[5] . "-head";

                    $response->results[]	= $result;

                    //hop up three nodes to get the nav section
                    $parent = $simpleXmlHelper->getParentNode($value);
                    $parent = $simpleXmlHelper->getParentNode($parent);
                    $parent = $simpleXmlHelper->getParentNode($parent);
                    $result 				= new stdClass();
                    $result->found			= true;
                    $result->found_type		= 'nav';   //other option is 'group'
                    $result->found_label	= $helper->__((string) $parent->label);
                    $label_nav 				= $result->found_label;
                    $result->type 			= $type;
                    $response->results[]	= $result;

                    //and also point out the nav
                    $response->responseText[] = 'Configuration*/*' .
                    '<a href="' . $sectionUrl . '">' . $label_nav . '</a>' .
                    '*/*' .
                    '<a href="' . $groupUrl . '">' . $label_group . '</a>' .
                    '';
                } else if($type == Alanstormdotcom_Systemsearch_Model_Configsearch::TYPE_SYSTEM_CONFIG_FIELD) {
                    //hop up to group
                    $parent = $simpleXmlHelper->getParentNode($value);
                    $parent = $simpleXmlHelper->getParentNode($parent);
                    $parent = $simpleXmlHelper->getParentNode($parent);

                    $result 				= new stdClass();
                    $result->found			= true;
                    $result->found_type		= 'group';   //other option is 'group'
                    $result->found_label	= $helper->__((string) $parent->label);
                    $label_group			= $result->found_label;
                    $result->type 			= $type;

                    $response->results[]	= $result;

    // 				//hope up to section
                    $parent = $simpleXmlHelper->getParentNode($parent);
                    $parent = $simpleXmlHelper->getParentNode($parent);

                    $result 				= new stdClass();
                    $result->found			= true;
                    $result->found_type		= 'nav';   //other option is 'group'
                    $result->found_label	= $helper->__((string) $parent->label);
                    $label_nav				= $result->found_label;
                    $result->type 			= $type;
                    $result->path 			= $simpleXmlHelper->getPathExpression($value);
                    $path = explode("/", $result->path);
                    $sectionUrl = $adminModel->getUrl("*/system_config/edit", array("section" => $path[3]));
                    $groupUrl = $sectionUrl . "#" . $path[3] . "_" . $path[5] . "-head";

                    $response->results[]	= $result;

                    $response->responseText[] = 'Configuration*/*' .
                    '<a href="' . $sectionUrl . '">' . $label_nav . '</a>' .
                    '*/*' .
                        '<a href="' . $groupUrl . '">' . $label_group . '</a>' .
                    '*/*' .
                    (string) $value .
                    ' (' . $path[3] . "/" . $path[5] . "/" . $path[7] . ')';
                }
            }
        }

        $response->responseText = $this->fetchLayout()->createBlock('alanstormdotcomsystemsearch/searchresults')
            ->addResultsArray($response->responseText)
            ->toHtml();

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    protected function fetchSimpleXmlHelper()
    {
        return Mage::helper('alanstormdotcomsystemsearch/simplexml');
    }

    private function fetchLayout()
    {
        return Mage::getSingleton('core/layout');
    }

}