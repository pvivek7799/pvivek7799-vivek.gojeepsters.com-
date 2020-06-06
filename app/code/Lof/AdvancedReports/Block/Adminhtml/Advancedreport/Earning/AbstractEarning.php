<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Earning;

class AbstractEarning extends \Magento\Backend\Block\Template
{
    protected $_columnDate = 'main_table.created_at';
	protected $_limit = 10;
    protected $_storeManager;
    protected $_helperData;
    protected $_objectManager;
    protected $_registry;
    protected $_localeCurrency;

    /**
     * Format price by specified website
     *
     * @param float $price
     * @param null|int $websiteId
     * @return string
     */
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    public $localeLists;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,     
        \Lof\AdvancedReports\Helper\Data $helperData, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
        )
    {  
        parent::__construct($context, $data);  
        $this->localeLists = $localeLists;
        $this->_helperData = $helperData;
        $this->_storeManager = $context->getStoreManager();
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_localeCurrency = $localeCurrency;   
    }
    public function formatCurrency($price, $websiteId = null)
    {
        return $this->_storeManager->getWebsite($websiteId)->getBaseCurrency()->format($price);
    }
           /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Earning\Collection';
    }

}