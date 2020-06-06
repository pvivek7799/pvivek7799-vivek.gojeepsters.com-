<?php
/**
 * Chase Payment Module.
 *
 * @category  Payment Integration
 * @package   Rootways_Chase
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2017 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/shop/media/extension_doc/license_agreement.pdf
 */

namespace Rootways\Chase\Helper;

use Magento\Payment\Model\Config as PaymentConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
    */
    protected $_objectManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
    */
    protected $_encryptor;
    
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
    */
    protected $resourceConfig;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
    */
    protected $regionFactory;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
    */
    protected $countryFactory;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_customresourceConfig = $resourceConfig;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context);
    }
    
    public function getConfig($config_path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    
    /**
     * Chase payment gateway URL.
    */ 
    public function getGatewayUrl($storeId)
    {
        if ($this->getConfig('payment/rootways_chase_option/account_mode', $storeId) == 1) {
            $url = $this->getConfig('payment/rootways_chase_option/gateway_url', $storeId);
        } else {
            $url = 'https://orbitalvar1.chasepaymentech.com/authorize';
        }
        return $url;
    }
    
    /**
     * Get API Username
    */
    public function getUsername($storeId)
    {
        $username = $this->getConfig('payment/rootways_chase_option/username', $storeId);
        return $this->_encryptor->decrypt($username);
    }
    
    /**
     * Get API Password
    */
    public function getPassword($storeId)
    {
        $password = $this->getConfig('payment/rootways_chase_option/password', $storeId);
        return $this->_encryptor->decrypt($password);
    }
    
    /**
     * Get API Merchant ID
    */
    public function getMerchantID($storeId)
    {
        return $this->getConfig('payment/rootways_chase_option/merchant_id', $storeId);
    }
    
    /**
     * Get API Terminal ID
    */
    public function getTerminalID($storeId)
    {
        return $this->getConfig('payment/rootways_chase_option/terminal_id', $storeId);
    }
    
    /**
     * Get API Bin
    */
    public function getBin($storeId)
    {
        return $this->getConfig('payment/rootways_chase_option/bin', $storeId);
    }
    
    /**
     * Get value of licence key from admin
    */
    public function licencekey()
    {
        return $this->getConfig('rootways_chase/general/licencekey');
    }
    
    /**
     * Get store base URL.
    */
    public function getStoreBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    
    /**
     * Get value of licence key from admin
    */
    public function act()
    {
        
    }
    
    /**
     * Get value of secure URL from admin
    */
    public function surl()
    {
        return "aHR0cHM6Ly9yb290d2F5cy5jb20vc2hvcC9tMl9leHRsYy5waHA=";
    }
    
    /**
     * Get value of Region Code.
    */
    public function getRegionCode($shipperRegionId)
    {
        $shipperRegion = $this->_regionFactory->create()->load($shipperRegionId );
        return $shipperRegion->getCode();
    }
    
    /**
     * Get value of Country ID.
    */
    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}
