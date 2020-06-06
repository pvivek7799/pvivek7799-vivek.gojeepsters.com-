<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ScopeCurrency
 *
 * @package Aheadworks\AdvancedReports\Ui
 */
class ScopeCurrency
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get scope currency code
     *
     * @param array|int|null
     * @return string
     */
    public function getCurrencyCode($storeIds)
    {
        if (!empty($storeIds)) {
            $storeId = array_shift($storeIds);
            $store = $this->storeManager->getStore($storeId);
            $currencyCode = $store->getBaseCurrency()->getCode();
        } else {
            $currencyCode = $this->scopeConfig->getValue(
                Currency::XML_PATH_CURRENCY_DEFAULT,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }

        return $currencyCode;
    }
}
