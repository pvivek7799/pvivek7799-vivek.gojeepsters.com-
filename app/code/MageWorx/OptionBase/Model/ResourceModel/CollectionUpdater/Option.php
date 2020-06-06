<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterAbstract;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionBase\Model\Product\Option\CollectionUpdaters as OptionCollectionUpdaters;
use MageWorx\OptionBase\Model\Product\Option\Value\CollectionUpdaters as ValueCollectionUpdaters;
use Magento\Framework\App\State;
use MageWorx\OptionBase\Helper\Data;
use MageWorx\OptionBase\Helper\CustomerVisibility;

class Option extends CollectionUpdaterAbstract
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var CustomerVisibility
     */
    protected $helperCustomerVisibility;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var int
     */
    protected $customerStoreId;

    /**
     * @var bool
     */
    protected $isEnabledCustomerGroup;

    /**
     * @var bool
     */
    protected $isEnabledCustomerStoreView;

    /**
     * @var bool
     */
    protected $isVisibilityFilterRequired;

    /**
     * Option constructor.
     *
     * @param ResourceConnection $resource
     * @param Collection $collection
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param OptionCollectionUpdaters $optionCollectionUpdaters
     * @param ValueCollectionUpdaters $valueCollectionUpdaters
     * @param State $state
     * @param Data $helperData
     * @param CustomerVisibility $helperCustomerVisibility
     * @param array $conditions
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        ResourceConnection $resource,
        Collection $collection,
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        OptionCollectionUpdaters $optionCollectionUpdaters,
        ValueCollectionUpdaters $valueCollectionUpdaters,
        State $state,
        Data $helperData,
        CustomerVisibility $helperCustomerVisibility,
        $conditions = []
    ) {
        parent::__construct(
            $collection,
            $resource,
            $collectionUpdaterRegistry,
            $optionCollectionUpdaters,
            $valueCollectionUpdaters,
            $helperData,
            $conditions
        );

        $this->connection                 = $resource->getConnection();
        $this->state                      = $state;
        $this->helperCustomerVisibility   = $helperCustomerVisibility;
        $this->isEnabledCustomerGroup     = $this->helperData->isEnabledVisibilityPerCustomerGroup();
        $this->isEnabledCustomerStoreView = $this->helperData->isEnabledVisibilityPerCustomerStoreView();
        $this->customerGroupId            = $this->helperCustomerVisibility->getCurrentCustomerGroupId();
        $this->customerStoreId            = $this->helperCustomerVisibility->getCurrentCustomerStoreId();
        $this->isVisibilityFilterRequired = $this->helperCustomerVisibility->isVisibilityFilterRequired();
    }

    /**
     * Process option collection by updaters
     */
    public function process()
    {
        $partFrom = $this->collection->getSelect()->getPart('from');

        if ($this->isVisibilityFilterRequired && $this->helperData->isEnabledIsDisabled()) {
            $this->collection->addFieldToFilter('main_table.disabled', '0');
            $this->collection->addFieldToFilter('main_table.disabled_by_values', '0');
        }

        foreach ($this->optionCollectionUpdaters->getData() as $optionCollectionUpdatersItem) {
            $alias = $optionCollectionUpdatersItem->getTableAlias();
            if (array_key_exists($alias, $partFrom)) {
                continue;
            }

            $this->collection->getSelect()->joinLeft(
                $optionCollectionUpdatersItem->getFromConditions($this->conditions),
                $optionCollectionUpdatersItem->getOnConditionsAsString(),
                $optionCollectionUpdatersItem->getColumns()
            );
        }

        if ($this->isVisibilityFilterRequired && $this->isEnabledCustomerGroup) {
            $this->collection->getSelect()->having('visibility_by_customer_group_id', $this->customerGroupId);
        }

        if ($this->isVisibilityFilterRequired && $this->isEnabledCustomerStoreView) {
            $this->collection->getSelect()->having('visibility_by_customer_store_id', $this->customerStoreId);
        }
    }
}