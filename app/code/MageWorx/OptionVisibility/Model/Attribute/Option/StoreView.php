<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionVisibility\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionVisibility\Model\OptionStoreView as StoreViewModel;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class StoreView extends AbstractAttribute
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var StoreViewModel
     */
    protected $storeViewModel;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param StoreViewModel $storeViewModel
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        StoreViewModel $storeViewModel,
        SystemHelper $systemHelper
    ) {
        $this->helper         = $helper;
        $this->systemHelper   = $systemHelper;
        $this->storeViewModel = $storeViewModel;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return StoreViewModel::KEY_STORE_VIEW;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
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
            'product' => StoreViewModel::TABLE_NAME,
            'group'   => StoreViewModel::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isVisibilityStoreViewEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $storeGroups = [];
        foreach ($options as $option) {
            if (empty($option) || !isset($option[$this->getName()])) {
                continue;
            }
            $storeGroups[$option['option_id']] = $option[$this->getName()];
        }

        return $this->collectStoreView($storeGroups);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function collectStoreView($items)
    {
        $data     = [];
        $storeIds = $this->systemHelper->getStoreIds();

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                StoreViewModel::COLUMN_NAME_OPTION_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }

            $isAllStores = false;
            foreach ($decodedJsonData as $key => $dataItem) {
                if ($dataItem[StoreViewModel::COLUMN_NAME_STORE_ID] == '0') {
                    $isAllStores = true;
                    break;
                }
            }
            if ($isAllStores) {
                continue;
            }

            foreach ($decodedJsonData as $key => $dataItem) {
                if (!in_array($dataItem[StoreViewModel::COLUMN_NAME_STORE_ID], array_values($storeIds))) {
                    continue;
                }
                $data['save'][] = [
                    StoreViewModel::COLUMN_NAME_OPTION_ID => $itemKey,
                    StoreViewModel::COLUMN_NAME_STORE_ID  =>
                        (int)$dataItem[StoreViewModel::COLUMN_NAME_STORE_ID]
                ];
            }
        }

        return $data;
    }

    /**
     * Delete old option value special prices
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[StoreViewModel::COLUMN_NAME_OPTION_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = StoreViewModel::COLUMN_NAME_OPTION_ID .
            " IN (" . implode(",", $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     * @return array
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * Process attribute in case of product/group duplication
     *
     * @param string $newId
     * @param string $oldId
     * @param string $entityType
     * @return void
     */
    public function processDuplicate($newId, $oldId, $entityType = 'product')
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName($this->getTableName($entityType));

        $select = $connection->select()->from(
            $table,
            [
                new \Zend_Db_Expr($newId),
                StoreViewModel::COLUMN_NAME_STORE_ID
            ]
        )->where(
            StoreViewModel::COLUMN_NAME_OPTION_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                StoreViewModel::COLUMN_NAME_OPTION_ID,
                StoreViewModel::COLUMN_NAME_STORE_ID
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        if (!isset($data['store_views']) || !is_array($data['store_views'])) {
            return json_encode([]);
        }
        $preparedData = [];
        foreach ($data['store_views'] as $storeId) {
            $preparedData[] = [
                StoreViewModel::COLUMN_NAME_STORE_ID => $storeId
            ];
        }

        return json_encode($preparedData);
    }
}