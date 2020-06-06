<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\Attribute\Value;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionBase\Model\OptionTypePrice;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Price extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param ResourceConnection $resource
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        ResourceConnection $resource,
        BaseHelper $baseHelper
    ) {
        $this->baseHelper = $baseHelper;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return OptionTypePrice::KEY_MAGEWORX_OPTION_TYPE_PRICE;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => OptionTypePrice::TABLE_NAME,
            'group'   => OptionTypePrice::OPTIONTEMPLATES_TABLE_NAME,
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }
        return $map[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, array $options)
    {
        $this->entity               = $entity;
        $currentStoreId             = (int)$entity->getDataObject()->getData('store_id') ?: 0;
        $isWebsiteCatalogPriceScope = $this->baseHelper->isWebsiteCatalogPriceScope();

        $savedItems = [];
        $items      = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $savedItems[$value[OptionTypePrice::FIELD_OPTION_TYPE_ID]] = $value[$this->getName()];

                if (!$isWebsiteCatalogPriceScope && $currentStoreId !== Store::DEFAULT_STORE_ID) {
                    continue;
                }
                $items[$value[OptionTypePrice::FIELD_OPTION_TYPE_ID]] = [
                    OptionTypePrice::FIELD_PRICE      => $value[OptionTypePrice::FIELD_PRICE],
                    OptionTypePrice::FIELD_PRICE_TYPE => $value[OptionTypePrice::FIELD_PRICE_TYPE],
                    OptionTypePrice::FIELD_STORE_ID   => $currentStoreId
                ];
            }
        }

        return $this->collectPrices($items, $savedItems);
    }

    /**
     * Collect option value prices
     *
     * @param array $items
     * @param array $savedItems
     * @return array
     */
    protected function collectPrices($items, $savedItems)
    {
        $data = [];

        foreach ($savedItems as $savedItemKey => $savedItemValue) {
            $decodedJsonData = json_decode($savedItemValue, true);
            if (empty($decodedJsonData)) {
                continue;
            }
            $data['delete'][] = [
                OptionTypePrice::FIELD_OPTION_TYPE_ID => $savedItemKey,
            ];
            $this->mergeNewPrices($decodedJsonData, $items, $savedItemKey);
            foreach ($decodedJsonData as $dataItem) {
                $data['save'][] = [
                    OptionTypePrice::FIELD_OPTION_TYPE_ID => $savedItemKey,
                    OptionTypePrice::FIELD_STORE_ID       => $dataItem[OptionTypePrice::FIELD_STORE_ID],
                    OptionTypePrice::FIELD_PRICE          => $dataItem[OptionTypePrice::FIELD_PRICE],
                    OptionTypePrice::FIELD_PRICE_TYPE     => $dataItem[OptionTypePrice::FIELD_PRICE_TYPE]
                ];
            }
        }
        return $data;
    }

    /**
     * Merge new prices with the old ones
     * Prepare data before save to db, used because we re-insert all prices for all store views
     *
     * @param array $decodedJsonData
     * @param array $items
     * @param int $savedItemKey
     */
    protected function mergeNewPrices(&$decodedJsonData, $items, $savedItemKey)
    {
        foreach ($items as $itemKey => $itemData) {
            if ($itemKey != $savedItemKey) {
                continue;
            }
            $storeId        = $itemData[OptionTypePrice::FIELD_STORE_ID];
            $storePrice     = $itemData[OptionTypePrice::FIELD_PRICE];
            $storePriceType = $itemData[OptionTypePrice::FIELD_PRICE_TYPE];
            if ($storePrice === '') {
                if (is_array($decodedJsonData) && isset($decodedJsonData[$storeId])) {
                    unset($decodedJsonData[$storeId]);
                }
                continue;
            }
            $isSaved = false;
            foreach ($decodedJsonData as $dataKey => $dataItem) {
                if ($dataItem[OptionTypePrice::FIELD_STORE_ID] == $storeId) {
                    $decodedJsonData[$dataKey][OptionTypePrice::FIELD_PRICE]      = $storePrice;
                    $decodedJsonData[$dataKey][OptionTypePrice::FIELD_PRICE_TYPE] = $storePriceType;
                    $isSaved                                                      = true;
                }
            }
            if ($isSaved) {
                continue;
            }
            $decodedJsonData[] = [
                OptionTypePrice::FIELD_STORE_ID   => $storeId,
                OptionTypePrice::FIELD_PRICE      => $storePrice,
                OptionTypePrice::FIELD_PRICE_TYPE => $storePriceType
            ];
        }
    }

    /**
     * Delete old mageworx option value prices
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[OptionTypePrice::FIELD_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypePrice::FIELD_OPTION_TYPE_ID . " IN (" . implode(',', $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }
}
