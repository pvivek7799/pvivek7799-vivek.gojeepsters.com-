<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\Attribute\Value;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionBase\Model\OptionTypeTitle;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Title extends AbstractAttribute implements AttributeInterface
{
    const FIELD_IS_USE_DEFAULT = 'is_use_default';

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
        return OptionTypeTitle::KEY_MAGEWORX_OPTION_TYPE_TITLE;
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
            'product' => OptionTypeTitle::TABLE_NAME,
            'group'   => OptionTypeTitle::OPTIONTEMPLATES_TABLE_NAME,
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
        $this->entity = $entity;
        $currentStoreId = $entity->getDataObject()->getData('store_id') ?: 0;

        $savedItems = [];
        $items = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $savedItems[$value[OptionTypeTitle::FIELD_OPTION_TYPE_ID]] = $value[$this->getName()];

                $title = empty($value[self::FIELD_IS_USE_DEFAULT]) ? $value[OptionTypeTitle::FIELD_TITLE] : '';
                $items[$value[OptionTypeTitle::FIELD_OPTION_TYPE_ID]] = [
                    $currentStoreId => $title
                ];
            }
        }

        return $this->collectTitles($items, $savedItems);
    }

    /**
     * Collect option value titles
     *
     * @param array $items
     * @param array $savedItems
     * @return array
     */
    protected function collectTitles($items, $savedItems)
    {
        $data = [];

        foreach ($savedItems as $savedItemKey => $savedItemValue) {
            $decodedJsonData  = json_decode($savedItemValue, true);
            if (empty($decodedJsonData)) {
                continue;
            }
            $data['delete'][] = [
                OptionTypeTitle::FIELD_OPTION_TYPE_ID => $savedItemKey,
            ];
            $this->mergeNewTitles($decodedJsonData, $items, $savedItemKey);
            foreach ($decodedJsonData as $dataItem) {
                $data['save'][] = [
                    OptionTypeTitle::FIELD_OPTION_TYPE_ID => $savedItemKey,
                    OptionTypeTitle::FIELD_STORE_ID => $dataItem[OptionTypeTitle::FIELD_STORE_ID],
                    OptionTypeTitle::FIELD_TITLE => $dataItem[OptionTypeTitle::FIELD_TITLE],
                ];
            }
        }
        return $data;
    }

    /**
     * Merge new titles with the old ones
     * Prepare data before save to db, used because we re-insert all titles for all store views
     *
     * @param array $decodedJsonData
     * @param array $items
     * @param int $savedItemKey
     */
    protected function mergeNewTitles(&$decodedJsonData, $items, $savedItemKey)
    {
        foreach ($items as $itemKey => $itemData) {
            if ($itemKey != $savedItemKey) {
                continue;
            }
            foreach ($itemData as $storeId => $storeTitle) {
                if ($storeTitle === '') {
                    if (is_array($decodedJsonData) && isset($decodedJsonData[$storeId])) {
                        unset($decodedJsonData[$storeId]);
                    }
                    continue;
                }
                $isSaved = false;
                foreach ($decodedJsonData as $dataKey => $dataItem) {
                    if ($dataItem[OptionTypeTitle::FIELD_STORE_ID] == $storeId) {
                        $decodedJsonData[$dataKey][OptionTypeTitle::FIELD_TITLE] = $storeTitle;
                        $isSaved = true;
                    }
                }
                if ($isSaved) {
                    continue;
                }
                $decodedJsonData[] = [
                    OptionTypeTitle::FIELD_STORE_ID => $storeId,
                    OptionTypeTitle::FIELD_TITLE => $storeTitle,
                ];
            }
        }
    }

    /**
     * Delete old mageworx option value titles
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[OptionTypeTitle::FIELD_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypeTitle::FIELD_OPTION_TYPE_ID . " IN (" . implode(',', $optionValueIds) . ")";
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
