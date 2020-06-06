<?php

namespace Lof\LayeredNavigation\Helper;

use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data  extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Lof\All\Helper\Data
     */
    protected $_helperAll;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;
    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Lof\All\Helper\Data $helperAll,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_remoteAddress = $remoteAddress;
        $this->_helperAll = $helperAll;
        $this->_scopeConfig = $scopeConfig;
        $this->objectManager   = $objectManager;
        $this->storeManager    = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return CurrentUrl | string
     */
    public function getCurrentUrl(){
        $model=$this->objectManager->get('Magento\Framework\UrlInterface');
        return $model->getCurrentUrl();
    }

	public function isEnabled($storeId = null)
    {
        $license = $this->_helperAll->getLicense('Lof_LayeredNavigation');
        $ip = $this->_remoteAddress->getRemoteAddress();
        if ($license->getStatus() == 0) {
            if ($ip == '127.0.0.1') {
                return $this->getConfigValue('layered_navigation/general/enable', $storeId);
            } else {
                $str = '0';
                return $str;
            }
        }else{

            return $this->getConfigValue('layered_navigation/general/enable', $storeId);
        }
    }

    public function getStoreConfig($storePath){
        $StoreConfig =  $this->_scopeConfig->getValue($storePath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $StoreConfig;
    }

    public function initProductLayout(ResultPage $resultPage,$layout_id)
    {
        $post_list_layout = $this->getStoreConfig($layout_id);
        $pageConfig = $resultPage->getConfig();
        $pageConfig->setPageLayout($post_list_layout);
        return $this;
    }

    public function prepareAndRender(ResultPage $resultPage, $controller)
    {
        $this->initProductLayout($resultPage,'layered_navigation/general/typelayer');
        return $this;
    }
}
