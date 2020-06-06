<?php
namespace Rootways\Chase\Block\System\Config;

use Magento\Framework\Registry;

class Heading extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_coreRegistry;
    
    protected $helper;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Rootways\Chase\Helper\Data $helper,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }
    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $base = $this->getBaseUrl();
        $html = '';
        $status = base64_decode($this->_helper->act());
        $a = base64_decode('RXh0ZW5zaW9uIEFjdGl2YXRlZC4=');
        if ($status != '') {
        $html .= <<<HTML
        <div style="margin-top: 5px;color: red;">$status</div>
HTML;
    } else {
            $html .= <<<HTML
        <div style="margin-top:  7px;color: #3bb53b;font-weight: bold;">$a</div>
HTML;
        }
        return $html;
    }
}
