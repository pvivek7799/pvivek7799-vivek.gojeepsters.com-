<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Config\Backend;

use Aheadworks\AdvancedReports\Model\ResourceModel\CustomerSales\Range as CustomerSalesRangeResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistor;

/**
 * Class Range
 * @package Magento\CatalogInventory\Model\System\Config\Backend
 */
class Range extends \Magento\Framework\App\Config\Value
{
    /**
     * Config range key
     */
    const CONFIG_RANGE_KEY = 'aw_arep_config_range';

    /**
     * @var CustomerSalesRangeResource
     */
    private $customerSalesRangeResource;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistor
     */
    private $dataPersistor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param CustomerSalesRangeResource $customerSalesRangeResource
     * @param RequestInterface $request
     * @param DataPersistor $dataPersistor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        CustomerSalesRangeResource $customerSalesRangeResource,
        RequestInterface $request,
        DataPersistor $dataPersistor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSalesRangeResource = $customerSalesRangeResource;
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $websiteId = (int) $this->request->getParam('website', 0);
        $savedValue = $this->dataPersistor->get(self::CONFIG_RANGE_KEY);
        if ($savedValue) {
            $value = $savedValue;
            $this->dataPersistor->clear(self::CONFIG_RANGE_KEY);
        } else {
            $value = $this->customerSalesRangeResource->loadConfigValue($websiteId);
        }
        $this->setValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->prepareForSave($value);
        $this->setRangeValue($value);
        $this->setValue(serialize($value));
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave()
    {
        $websiteId = (int) $this->request->getParam('website', 0);
        $this->customerSalesRangeResource->saveConfigValue($this->getRangeValue(), $websiteId);
        $this->dataPersistor->clear(self::CONFIG_RANGE_KEY);
        return parent::afterSave();
    }

    /**
     * {@inheritDoc}
     */
    public function afterDelete()
    {
        $websiteId = (int) $this->request->getParam('website', 0);
        $this->customerSalesRangeResource->removeConfigValue($websiteId);
        return parent::afterDelete();
    }

    /**
     * Prepare config value for save
     *
     * @param [] $value
     * @return []
     */
    private function prepareForSave($value)
    {
        unset($value['__empty']);
        $value = $this->removeDuplicates($value);
        $this->dataPersistor->set(self::CONFIG_RANGE_KEY, $value);
        $this->validate($value);

        return $value;
    }

    /**
     * Remove duplicates
     *
     * @param [] $value
     * @return []
     */
    private function removeDuplicates($value)
    {
        $resultValue = [];
        foreach ($value as $valueRow) {
            if (!in_array($valueRow, $resultValue)) {
                $resultValue[] = $valueRow;
            }
        }
        return $resultValue;
    }

    /**
     * Validate config value before save
     *
     * @param [] $value
     * @throws \Exception
     * @return $this
     */
    private function validate($value)
    {
        $this-->$this->validateFrom($value);
        $this-->$this->validateTo($value);
        $this-->$this->validateRanges($value);

        return $this;
    }

    /**
     * Validate Range From
     *
     * @param [] $value
     * @throws \Exception
     * @return $this
     */
    private function validateFrom($value)
    {
        $zeroCount = 0;
        foreach ($value as $valueRow) {
            if ($valueRow['range_from'] == 0) {
                $zeroCount++;
            }
        }
        if ($zeroCount > 1) {
            throw new \Exception(__("Only one zero From value is possible"));
        }
        return $this;
    }

    /**
     * Validate Range To
     *
     * @param [] $value
     * @throws \Exception
     * @return $this
     */
    private function validateTo($value)
    {
        $infinityCount = 0;
        foreach ($value as $valueRow) {
            if ($valueRow['range_to'] == '') {
                $infinityCount++;
            }
        }
        if ($infinityCount > 1) {
            throw new \Exception(__("Only one empty To value is possible"));
        }
        return $this;
    }

    /**
     * Validate Ranges
     *
     * @param [] $value
     * @throws \Exception
     * @return $this
     */
    private function validateRanges($value)
    {
        foreach ($value as $valueRow) {
            if ($valueRow['range_to'] != '' && $valueRow['range_from'] >= $valueRow['range_to']) {
                throw new \Exception(__("From value should be less than To value"));
            }
        }

        $maxTo = 0;
        foreach ($value as $valueRow) {
            if ($valueRow['range_from'] >= $maxTo) {
                $maxTo = $valueRow['range_from'] + 1;
            }
            if ($valueRow['range_to'] != '' && $valueRow['range_to'] >= $maxTo) {
                $maxTo = $valueRow['range_to'] + 1;
            }
        }
        foreach ($value as $valueIndex => $valueRow) {
            if ($valueRow['range_to'] == '') {
                $valueRow['range_to'] = $maxTo;
            }
            foreach ($value as $checkIndex => $checkRow) {
                if ($checkRow['range_to'] == '') {
                    $checkRow['range_to'] = $maxTo;
                }
                if ($valueIndex != $checkIndex) {
                    if ((
                            $valueRow['range_from'] >= $checkRow['range_from']
                            && $valueRow['range_from'] <= $checkRow['range_to'])
                        || (
                            $valueRow['range_to'] >= $checkRow['range_from']
                            && $valueRow['range_to'] <= $checkRow['range_to']
                        )
                        || (
                            $valueRow['range_from'] >= $checkRow['range_from']
                            && $valueRow['range_to'] <= $checkRow['range_to']
                        )
                    ) {
                        throw new \Exception(__("Ranges can not be overlapped"));
                    }
                }
            }
        }
        return $this;
    }
}
