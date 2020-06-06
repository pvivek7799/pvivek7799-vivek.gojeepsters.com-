<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionVisibility\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionVisibility\Model\OptionCustomerGroup as CustomerGroupModel;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class CustomerGroup extends AbstractAttribute
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
     * @var CustomerGroupModel
     */
    protected $customerGroupModel;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param CustomerGroupModel $customerGroupModel
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        CustomerGroupModel $customerGroupModel,
        SystemHelper $systemHelper
    ) {
        $this->helper             = $helper;
        $this->systemHelper       = $systemHelper;
        $this->customerGroupModel = $customerGroupModel;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return CustomerGroupModel::KEY_CUSTOMER_GROUP;
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
            'product' => CustomerGroupModel::TABLE_NAME,
            'group'   => CustomerGroupModel::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isVisibilityCustomerGroupEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $customerGroups = [];
        foreach ($options as $option) {
            if (empty($option) || !isset($option[$this->getName()])) {
                continue;
            }
            $customerGroups[$option['option_id']] = $option[$this->getName()];
        }

        return $this->collectCustomerGroup($customerGroups);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function collectCustomerGroup($items)
    {
        $data             = [];
        $customerGroupIds = $this->systemHelper->getCustomerGroupIds();

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                CustomerGroupModel::COLUMN_NAME_OPTION_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }

            $isAllGroups = false;
            foreach ($decodedJsonData as $key => $dataItem) {
                if ($dataItem[CustomerGroupModel::COLUMN_NAME_GROUP_ID] == '32000') {
                    $isAllGroups = true;
                    break;
                }
            }
            if ($isAllGroups) {
                continue;
            }

            foreach ($decodedJsonData as $key => $dataItem) {
                if (!in_array($dataItem[CustomerGroupModel::COLUMN_NAME_GROUP_ID], array_values($customerGroupIds))) {
                    continue;
                }
                $data['save'][] = [
                    CustomerGroupModel::COLUMN_NAME_OPTION_ID => $itemKey,
                    CustomerGroupModel::COLUMN_NAME_GROUP_ID  =>
                        (int)$dataItem[CustomerGroupModel::COLUMN_NAME_GROUP_ID]
                ];
            }
        }

        return $data;
    }

    /**
     * Delete old option value
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[CustomerGroupModel::COLUMN_NAME_OPTION_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = CustomerGroupModel::COLUMN_NAME_OPTION_ID .
            " IN (" . implode(",", $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value|array $data
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
                CustomerGroupModel::COLUMN_NAME_GROUP_ID
            ]
        )->where(
            CustomerGroupModel::COLUMN_NAME_OPTION_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                CustomerGroupModel::COLUMN_NAME_OPTION_ID,
                CustomerGroupModel::COLUMN_NAME_GROUP_ID
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        $preparedData = [];
        if (!isset($data['customer_groups']) || !is_array($data['customer_groups'])) {
            return json_encode($preparedData);
        }
        foreach ($data['customer_groups'] as $customerGroupId) {
            $preparedData[] = [
                CustomerGroupModel::COLUMN_NAME_GROUP_ID => $customerGroupId
            ];
        }

        return json_encode($preparedData);
    }
}