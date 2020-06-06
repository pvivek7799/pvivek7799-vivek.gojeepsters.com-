<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Aheadworks\AdvancedReports\Model\ResourceModel\DatesGrouping\Factory as DatesGroupingFactory;
use Magento\Framework\App\CacheInterface;
use Aheadworks\AdvancedReports\Model\ResourceModel\DatesGrouping;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Config
 *
 * @package Aheadworks\AdvancedReports\Model
 */
class Config
{
    /**
     * @var string
     */
    const MIN_DATE_CACHE_KEY = 'aw_arep_period_firstdate';

    /**#@+
     * Constants for config path
     */
    const XML_PATH_GENERAL_ORDER_STATUS = 'aw_advancedreports/general/order_status';
    const XML_PATH_GENERAL_MANUFACTURER_ATTRIBUTE = 'aw_advancedreports/general/manufacturer_attribute';
    const XML_PATH_GENERAL_LOCALE_FIRSTDAY = 'general/locale/firstday';
    const XML_PATH_GENERAL_REGION_STATE_REQUIRED = 'general/region/state_required';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var DatesGroupingFactory
     */
    private $datesGroupingFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CacheInterface $cache
     * @param DatesGroupingFactory $datesGroupingFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CacheInterface $cache,
        DatesGroupingFactory $datesGroupingFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->datesGroupingFactory = $datesGroupingFactory;
    }

    /**
     * Get order status
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_ORDER_STATUS);
    }

    /**
     * Get manufacturer attribute
     *
     * @return string
     */
    public function getManufacturerAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_MANUFACTURER_ATTRIBUTE);
    }

    /**
     * Get locale first day of week
     *
     * @return string
     */
    public function getFirstDayOfWeek()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_LOCALE_FIRSTDAY);
    }

    /**
     * Get countries with state required
     *
     * @return array
     */
    public function getCountriesWithStateRequired()
    {
        $value =  $this->scopeConfig->getValue(self::XML_PATH_GENERAL_REGION_STATE_REQUIRED);
        $countries = preg_split('/\,/', $value, 0, PREG_SPLIT_NO_EMPTY);
        return $countries;
    }

    /**
     * Retrieve first available date as string
     *
     * @return string
     */
    public function getFirstAvailableDate()
    {
        if (!$minDate = $this->cache->load(self::MIN_DATE_CACHE_KEY)) {
            try {
                $minDate = $this->datesGroupingFactory->create(DatesGrouping\Day::KEY)->getMinDate();
            } catch (LocalizedException $e) {
                return '';
            }
            $this->cache->save($minDate, self::MIN_DATE_CACHE_KEY, [], null);
        }
        return $minDate;
    }
}
