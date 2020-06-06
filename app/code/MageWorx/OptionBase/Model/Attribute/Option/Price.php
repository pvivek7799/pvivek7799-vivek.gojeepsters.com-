<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionBase\Model\OptionPrice;
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
     *
     * @return string
     */
    public function getName()
    {
        return OptionPrice::KEY_MAGEWORX_OPTION_PRICE;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $type
     * @return string
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => OptionPrice::TABLE_NAME,
            'group'   => OptionPrice::OPTIONTEMPLATES_TABLE_NAME,
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }
        return $map[$type];
    }

    /**
     * {@inheritdoc}
     *
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     * @param array $options
     * @return array
     */
    public function collectData($entity, array $options)
    {
        $this->entity               = $entity;
        $currentStoreId             = (int)$entity->getDataObject()->getData('store_id') ?: 0;
        $isWebsiteCatalogPriceScope = $this->baseHelper->isWebsiteCatalogPriceScope();

        $savedItems = [];
        $items      = [];
        foreach ($options as $option) {
            if (empty($option[$this->getName()])) {
                continue;
            }
            $savedItems[$option[OptionPrice::FIELD_OPTION_ID]] = $option[$this->getName()];

            if (!$isWebsiteCatalogPriceScope && $currentStoreId !== Store::DEFAULT_STORE_ID) {
                continue;
            }
            $items[$option[OptionPrice::FIELD_OPTION_ID]] = [
                OptionPrice::FIELD_PRICE      => $option[OptionPrice::FIELD_PRICE],
                OptionPrice::FIELD_PRICE_TYPE => $option[OptionPrice::FIELD_PRICE_TYPE],
                OptionPrice::FIELD_STORE_ID   => $currentStoreId
            ];
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
                OptionPrice::FIELD_OPTION_ID => $savedItemKey,
            ];
            $this->mergeNewPrices($decodedJsonData, $items, $savedItemKey);
            foreach ($decodedJsonData as $dataItem) {
                $data['save'][] = [
                    OptionPrice::FIELD_OPTION_ID  => $savedItemKey,
                    OptionPrice::FIELD_STORE_ID   => $dataItem[OptionPrice::FIELD_STORE_ID],
                    OptionPrice::FIELD_PRICE      => $dataItem[OptionPrice::FIELD_PRICE],
                    OptionPrice::FIELD_PRICE_TYPE => $dataItem[OptionPrice::FIELD_PRICE_TYPE]
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
            $storeId        = $itemData[OptionPrice::FIELD_STORE_ID];
            $storePrice     = $itemData[OptionPrice::FIELD_PRICE];
            $storePriceType = $itemData[OptionPrice::FIELD_PRICE_TYPE];
            if ($storePrice === '') {
                if (is_array($decodedJsonData) && isset($decodedJsonData[$storeId])) {
                    unset($decodedJsonData[$storeId]);
                }
                continue;
            }
            $isSaved = false;
            foreach ($decodedJsonData as $dataKey => $dataItem) {
                if ($dataItem[OptionPrice::FIELD_STORE_ID] == $storeId) {
                    $decodedJsonData[$dataKey][OptionPrice::FIELD_PRICE]      = $storePrice;
                    $decodedJsonData[$dataKey][OptionPrice::FIELD_PRICE_TYPE] = $storePriceType;
                    $isSaved                                                  = true;
                }
            }
            if ($isSaved) {
                continue;
            }
            $decodedJsonData[] = [
                OptionPrice::FIELD_STORE_ID   => $storeId,
                OptionPrice::FIELD_PRICE      => $storePrice,
                OptionPrice::FIELD_PRICE_TYPE => $storePriceType
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
        $optionIds = [];
        foreach ($data as $dataItem) {
            $optionIds[] = $dataItem[OptionPrice::FIELD_OPTION_ID];
        }
        if (!$optionIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionPrice::FIELD_OPTION_ID . " IN (" . implode(',', $optionIds) . ")";
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
