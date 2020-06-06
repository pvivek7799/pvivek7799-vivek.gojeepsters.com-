<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionBase\Model\OptionTitle;
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
     *
     * @return string
     */
    public function getName()
    {
        return OptionTitle::KEY_MAGEWORX_OPTION_TITLE;
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
            'product' => OptionTitle::TABLE_NAME,
            'group'   => OptionTitle::OPTIONTEMPLATES_TABLE_NAME,
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
        $this->entity = $entity;
        $currentStoreId = $entity->getDataObject()->getData('store_id') ?: 0;

        $savedItems = [];
        $items = [];
        foreach ($options as $option) {
            if (empty($option[$this->getName()])) {
                continue;
            }
            $savedItems[$option[OptionTitle::FIELD_OPTION_ID]] = $option[$this->getName()];

            $title = empty($option[self::FIELD_IS_USE_DEFAULT]) ? $option[OptionTitle::FIELD_TITLE] : '';
            $items[$option[OptionTitle::FIELD_OPTION_ID]] = [
                $currentStoreId => $title
            ];
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
                OptionTitle::FIELD_OPTION_ID => $savedItemKey,
            ];
            $this->mergeNewTitles($decodedJsonData, $items, $savedItemKey);
            foreach ($decodedJsonData as $dataItem) {
                $data['save'][] = [
                    OptionTitle::FIELD_OPTION_ID => $savedItemKey,
                    OptionTitle::FIELD_STORE_ID => $dataItem[OptionTitle::FIELD_STORE_ID],
                    OptionTitle::FIELD_TITLE => $dataItem[OptionTitle::FIELD_TITLE],
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
                    if ($dataItem[OptionTitle::FIELD_STORE_ID] == $storeId) {
                        $decodedJsonData[$dataKey][OptionTitle::FIELD_TITLE] = $storeTitle;
                        $isSaved = true;
                    }
                }
                if ($isSaved) {
                    continue;
                }
                $decodedJsonData[] = [
                    OptionTitle::FIELD_STORE_ID => $storeId,
                    OptionTitle::FIELD_TITLE => $storeTitle,
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
        $optionIds = [];
        foreach ($data as $dataItem) {
            $optionIds[] = $dataItem[OptionTitle::FIELD_OPTION_ID];
        }
        if (!$optionIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTitle::FIELD_OPTION_ID . " IN (" . implode(',', $optionIds) . ")";
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
