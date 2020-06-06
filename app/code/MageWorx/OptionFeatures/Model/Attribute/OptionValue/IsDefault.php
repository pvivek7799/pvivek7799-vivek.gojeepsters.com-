<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefault;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeIsDefault\Collection as IsDefaultCollection;
use MageWorx\OptionFeatures\Model\OptionTypeIsDefaultFactory as IsDefaultFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class IsDefault extends AbstractAttribute implements AttributeInterface
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
     * @var IsDefaultFactory
     */
    protected $isDefaultFactory;

    /**
     * @var IsDefaultCollection
     */
    protected $isDefaultCollection;

    /**
     * @param ResourceConnection $resource
     * @param IsDefaultFactory $isDefaultFactory
     * @param IsDefaultCollection $isDefaultCollection
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        IsDefaultFactory $isDefaultFactory,
        IsDefaultCollection $isDefaultCollection,
        Helper $helper,
        SystemHelper $systemHelper
    ) {
        $this->helper = $helper;
        $this->systemHelper = $systemHelper;
        $this->isDefaultFactory = $isDefaultFactory;
        $this->isDefaultCollection = $isDefaultCollection;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_IS_DEFAULT;
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
            'product' => OptionTypeIsDefault::TABLE_NAME,
            'group' => OptionTypeIsDefault::OPTIONTEMPLATES_TABLE_NAME
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

        $isDefaults = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            if ($option['type'] == 'radio' || $option['type'] == 'drop_down') {
                $isDefaultValueAlreadySelected = false;
                foreach ($option['values'] as $value) {
                    if (!isset($value[$this->getName()])) {
                        continue;
                    }
                    if ($value[$this->getName()] == 1 && !$isDefaultValueAlreadySelected) {
                        $isDefaults[$value['option_type_id']] = $value[$this->getName()];
                        $isDefaultValueAlreadySelected = true;
                    } else {
                        $isDefaults[$value['option_type_id']] = 0;
                    }
                }
            } else {
                foreach ($option['values'] as $value) {
                    if (!isset($value[$this->getName()])) {
                        continue;
                    }
                    $isDefaults[$value['option_type_id']] = $value[$this->getName()];
                }
            }
        }

        return $this->collectDefaults($isDefaults);
    }

    /**
     * Save defaults
     *
     * @param array $items
     * @return array
     */
    protected function collectDefaults($items)
    {
        $data = [];
        foreach ($items as $itemKey => $itemValue) {

            $data['delete'][] = [
                OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID => $itemKey
            ];
            if (!$itemValue) {
                continue;
            }
            $data['save'][] = [
                OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID => $itemKey,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID => 0,
                $this->getName() => $itemValue
            ];
        }
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * Delete old option value defaults
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID .
            " IN (" . "'" . implode("','", $optionValueIds) . "'" . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
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
        $table = $this->resource->getTableName($this->getTableName($entityType));

        $select = $connection->select()->from(
            $table,
            [
                new \Zend_Db_Expr($newId),
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                OptionTypeIsDefault::COLUMN_NAME_IS_DEFAULT
            ]
        )->where(
            OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                OptionTypeIsDefault::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeIsDefault::COLUMN_NAME_STORE_ID,
                OptionTypeIsDefault::COLUMN_NAME_IS_DEFAULT
            ]
        );
        $connection->query($insertSelect);
    }
}
