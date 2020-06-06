<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\Attribute;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionDependency\Helper\Data as Helper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionDependency\Model\Config;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use MageWorx\OptionDependency\Model\Converter;
use Magento\Framework\Registry;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Dependency extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var string
     */
    protected $saveSql = "";

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var Helper
     */
    protected $options;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     *
     * @var Registry
     */
    protected $registry;

    /**
     *
     * @var Config
     */
    protected $dependencyConfig;

    /**
     *
     * @var bool
     */
    protected $isAfterTemplate = false;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param JsonHelper $jsonHelper
     * @param Converter $converter
     * @param Registry $registry
     * @param Config $dependencyConfig
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        Converter $converter,
        Registry $registry,
        Config $dependencyConfig,
        JsonHelper $jsonHelper
    ) {
        $this->helper = $helper;
        $this->converter = $converter;
        $this->registry = $registry;
        $this->dependencyConfig = $dependencyConfig;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dependency';
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
            'product' => Config::TABLE_NAME,
            'group' => Config::OPTIONTEMPLATES_TABLE_NAME
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
        $this->options = $options;
        $this->isAfterTemplate = $this->entity->getIsAfterTemplate();

        $collectedDependencies = $this->collectDependencies();
        if (!$collectedDependencies) {
            return [];
        }
        return $collectedDependencies;
    }

    /**
     * Delete old dependencies
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $connection = $this->resource->getConnection();

        if ($this->entity->getType() == 'group') {

            $groupIds = [];
            foreach ($data as $dataItem) {
                if (!empty($dataItem[Config::COLUMN_NAME_GROUP_ID])
                    && $dataItem[Config::COLUMN_NAME_GROUP_ID]
                    && !in_array($dataItem[Config::COLUMN_NAME_GROUP_ID], $groupIds)
                ) {
                    $groupIds[] = $dataItem[Config::COLUMN_NAME_GROUP_ID];
                }
            }
            if (!$groupIds) {
                return;
            }
            $tableName = $this->resource->getTableName($this->getTableName());

            $connection->delete(
                $tableName,
                [
                    Config::COLUMN_NAME_GROUP_ID . ' IN (?)' => implode(',', $groupIds)
                ]
            );

        } elseif ($this->entity->getType() == 'product') {

            $groupIds = [];
            $productIds = [];
            foreach ($data as $dataItem) {
                if (!empty($dataItem[Config::COLUMN_NAME_PRODUCT_ID])
                    && $dataItem[Config::COLUMN_NAME_PRODUCT_ID]
                    && !in_array($dataItem[Config::COLUMN_NAME_PRODUCT_ID], $productIds)
                ) {
                    $productIds[] = $dataItem[Config::COLUMN_NAME_PRODUCT_ID];
                }
                if (!empty($dataItem[Config::COLUMN_NAME_GROUP_ID])
                    && $dataItem[Config::COLUMN_NAME_GROUP_ID]
                    && !in_array($dataItem[Config::COLUMN_NAME_GROUP_ID], $groupIds)
                ) {
                    $groupIds[] = $dataItem[Config::COLUMN_NAME_GROUP_ID];
                }
            }
            if (!$productIds) {
                return;
            }

            $select = $connection->select()
                                 ->reset()
                                 ->from(['dep' => $this->resource->getTableName($this->getTableName())])
                                 ->joinLeft(
                                     ['cpo' => $this->resource->getTableName('catalog_product_option')],
                                     'cpo.option_id = dep.child_option_id',
                                     []
                                 );
            if ($this->entity->getDataObject()->getIsAfterTemplateSave()) {
                if (!$groupIds) {
                    return;
                }
                $select->where("dep.group_id IN (" . implode(',', $groupIds) . ") AND " .
                               "dep.product_id IN (" . implode(',', $productIds) . ")");
            } else {
                $select->where("dep.product_id IN (" . implode(',', $productIds) . ")");
            }
            $sql = $select->deleteFromSelect('dep');
            $connection->query($sql);

            $select = $connection->select()
                                 ->reset()
                                 ->from(['dep' => $this->resource->getTableName($this->getTableName())])
                                 ->joinLeft(
                                     ['cpo' => $this->resource->getTableName('catalog_product_option')],
                                     'cpo.option_id = dep.parent_option_id',
                                     []
                                 );
            if ($this->entity->getDataObject()->getIsAfterTemplateSave()) {
                $select->where("dep.group_id IN (" . implode(',', $groupIds) . ") AND " .
                               "dep.product_id IN (" . implode(',', $productIds) . ")");
            } else {
                $select->where("dep.product_id IN (" . implode(',', $productIds) . ")");
            }
            $sql = $select->deleteFromSelect('dep');
            $connection->query($sql);

        }
    }

    /**
     * Collect dependencies for future bulk save
     *
     * @return array
     */
    protected function collectDependencies()
    {
        if (empty($this->options)) {
            return [];
        }

        $data = [];
        foreach ($this->options as $option) {
            if (!$this->dependencyConfig->isSelectableOptionType($option['type'])) {
                $this->addData($data, $option);
            }

            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                $this->addData($data, $value);
            }
        }

        if (!$data) {
            return [];
        }

        if (!empty($data['save']) && is_array($data['save'])) {
            $data['save'] = array_unique($data['save'], SORT_REGULAR);
        }

        if (!empty($data['delete']) && is_array($data['delete'])) {
            $data['delete'] = array_unique($data['delete'], SORT_REGULAR);
        }

        return $data;
    }

    /**
     * Add dependencies data from object to overall data array
     * @param $data - option or value.
     * @param $object - option or value.
     * @return void
     */
    protected function addData(&$data, $object)
    {
        $childOptionId = isset($object['option_id']) ? $object['option_id'] : null;
        $childOptionTypeId = isset($object['option_type_id']) ? $object['option_type_id'] : '';
        $dataObjectId = $this->entity->getDataObjectId();
        $dependencies = isset($object['dependency']) ? $object['dependency'] : null;

        // exit if option or value has no dependencies
        if (is_null($dependencies)) {
            return;
        }

        $groupId = null;
        if ($this->entity->getType() == 'product') {
            $groupId           = $this->registry->registry('mageworx_optiontemplates_group_id');
            $data['delete'][]  = [
                Config::COLUMN_NAME_PRODUCT_ID => $dataObjectId,
                Config::COLUMN_NAME_GROUP_ID   => $groupId ? $groupId : 0,
            ];
        } else {
            $data['delete'][] = [
                Config::COLUMN_NAME_PRODUCT_ID => 0,
                Config::COLUMN_NAME_GROUP_ID => $dataObjectId,
            ];
        }

        if (!$dependencies) {
            return;
        }

        $savedDependencies = $this->jsonHelper->jsonDecode($dependencies);
        if ($this->entity->getType() == 'product' && !empty($object['need_to_process_dependency'])) {
            $savedDependencies = $this->convertDependencies($savedDependencies, $dataObjectId);
        }

        // delete non-existent options from dependencies
        $savedDependencies = $this->processDependencies($savedDependencies);
        if (!$this->isAfterTemplate) {
            $savedDependencies = $this->convertRecordIdToId($savedDependencies);
        }

        foreach ($savedDependencies as $dependency) {
            $parentOptionId = $dependency[0];
            $parentOptionTypeId = $dependency[1];
            if ($this->entity->getType() == 'product') {

                $groupOptionIds = $this->registry->registry('mageworx_optiontemplates_group_option_ids');
                if ($groupOptionIds) {
                    if (!$object['group_option_id']
                        || !in_array($object['group_option_id'], $groupOptionIds)
                        || (!$groupId && !empty($object['group_id']))
                    ) {
                        continue;
                    }
                }

                if (!empty($object['group_id'])) {
                    $groupId = $object['group_id'];
                }

                $data['save'][] = [
                    Config::COLUMN_NAME_CHILD_OPTION_ID => $childOptionId,
                    Config::COLUMN_NAME_CHILD_OPTION_TYPE_ID => (int)$childOptionTypeId,
                    Config::COLUMN_NAME_PARENT_OPTION_ID => $parentOptionId,
                    Config::COLUMN_NAME_PARENT_OPTION_TYPE_ID => $parentOptionTypeId,
                    $this->entity->getDataObjectIdName() => $dataObjectId,
                    Config::COLUMN_NAME_GROUP_ID => $groupId,
                    Config::COLUMN_NAME_IS_PROCESSED => '1'
                ];
            } else {
                $data['save'][] = [
                    Config::COLUMN_NAME_CHILD_OPTION_ID => $childOptionId,
                    Config::COLUMN_NAME_CHILD_OPTION_TYPE_ID => (int)$childOptionTypeId,
                    Config::COLUMN_NAME_PARENT_OPTION_ID => $parentOptionId,
                    Config::COLUMN_NAME_PARENT_OPTION_TYPE_ID => $parentOptionTypeId,
                    $this->entity->getDataObjectIdName() => $dataObjectId,
                    Config::COLUMN_NAME_IS_PROCESSED => '1'
                ];
            }
        }
        return;
    }

    /**
     * Convert group dependencies to product ones
     *
     * @param array $savedDependencies
     * @param int $dataObjectId
     * @return array
     */
    protected function convertDependencies($savedDependencies, $dataObjectId)
    {
        //convert magento_id on product
        $this->converter->setData($savedDependencies)
            ->setProductId($dataObjectId)
            ->setConvertTo(Converter::CONVERTING_MODE_MAGEWORX)
            ->setConvertWhere(Converter::CONVERTING_ENTITY_PRODUCT);
        return $this->converter->convert();
    }

    /**
     * Convert group dependencies to product ones
     *
     * @param array $savedDependencies
     * @return array
     */
    protected function processDependencies($savedDependencies)
    {
        $result = [];

        foreach ($savedDependencies as $key => $dependency) {
            if (!$this->isValidDependency($dependency)) {
                continue;
            }
            $result[$key] = $dependency;
        }

        return $result;
    }

    /**
     * Check if dependency is valid
     *
     * @param array $dependency
     * @return bool
     */
    protected function isValidDependency($dependency)
    {
        $isValueMatch = false;
        $isOptionMatch = false;
        $depOptionId = (string)$dependency[0];
        $depValueId = (string)$dependency[1];

        foreach ($this->options as $option) {
            $optionId = (string)$option['option_id'];
            $optionRecordId = isset($option['record_id']) ? (string)$option['record_id'] : '-1';

            if (!in_array($depOptionId, [$optionId, $optionRecordId])) {
                continue;
            }
            $isOptionMatch = true;

            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $value) {
                $valueId = (string)$value['option_type_id'];
                $valueRecordId = isset($value['record_id']) ? (string)$value['record_id'] : '-1';

                if (!in_array($depValueId, [$valueId, $valueRecordId])) {
                    continue;
                }
                $isValueMatch = true;
                break 2;
            }
        }

        return $isValueMatch && $isOptionMatch;
    }

    /**
     * Convert recordId to mageworxId
     *
     * @param array $savedDependencies
     * @return array
     */
    protected function convertRecordIdToId($savedDependencies)
    {
        $result = [];

        foreach ($savedDependencies as $key => $dependency) {
            $depOptionId = (string)$dependency[0];
            $depValueId = (string)$dependency[1];

            foreach ($this->options as $option) {
                $optionId = (string)$option['option_id'];
                $optionRecordId = isset($option['record_id']) ? (string)$option['record_id'] : '-1';

                if (!in_array($depOptionId, [$optionId, $optionRecordId])) {
                    continue;
                }
                $result[$key][0] = $optionId;

                $values = isset($option['values']) ? $option['values'] : [];
                foreach ($values as $value) {
                    $valueId = (string)$value['option_type_id'];
                    $valueRecordId = isset($value['record_id']) ? (string)$value['record_id'] : '-1';

                    if (!in_array($depValueId, [$valueId, $valueRecordId])) {
                        continue;
                    }
                    $result[$key][1] = $valueId;
                    break 2;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        if (empty($data['dependency']) || !is_array($data['dependency'])) {
            return '';
        }
        return json_encode($data['dependency']);
    }
}
