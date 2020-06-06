<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionTypeDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionTypeDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionTypeDescriptionFactory as DescriptionFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Description extends AbstractAttribute implements AttributeInterface
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
     * @var DescriptionFactory
     */
    protected $descriptionFactory;

    /**
     * @var DescriptionCollection
     */
    protected $descriptionCollection;

    /**
     * @param ResourceConnection $resource
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection,
        Helper $helper,
        SystemHelper $systemHelper
    ) {
        $this->helper                = $helper;
        $this->systemHelper          = $systemHelper;
        $this->descriptionFactory    = $descriptionFactory;
        $this->descriptionCollection = $descriptionCollection;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_DESCRIPTION;
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
            'product' => OptionTypeDescription::TABLE_NAME,
            'group'   => OptionTypeDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isValueDescriptionEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $descriptions[$value['option_type_id']] = $value[$this->getName()];
            }
        }

        return $this->collectDescriptions($descriptions);
    }

    /**
     * Collect descriptions
     *
     * @param array $items
     * @return array
     */
    protected function collectDescriptions($items)
    {
        $data = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }
            foreach ($decodedJsonData as $dataItem) {
                $description = str_replace(PHP_EOL, '', $dataItem[OptionTypeDescription::COLUMN_NAME_DESCRIPTION]);
                $description = str_replace('\\', '', $description);
                if ($description === '') {
                    continue;
                }
                $data['save'][] = [
                    OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID => $itemKey,
                    OptionTypeDescription::COLUMN_NAME_STORE_ID                =>
                        $dataItem[OptionTypeDescription::COLUMN_NAME_STORE_ID],
                    OptionTypeDescription::COLUMN_NAME_DESCRIPTION             =>
                        htmlspecialchars($description, ENT_COMPAT, 'UTF-8', false)
                ];
            }
        }
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * Delete old option value description
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID .
            " IN (" . "'" . implode("','", $optionValueIds) . "'" . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * Prepare attribute data for frontend js config
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     * @return array
     */
    public function prepareDataForFrontend($object)
    {
        $storeId = $this->systemHelper->resolveCurrentStoreId();
        $decodedJsonData  = json_decode($object->getData($this->getName()), true);
        if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
            return [$this->getName() => ''];
        }
        $description = '';
        $defaultStoreDescription = '';
        foreach ($decodedJsonData as $dataItem) {
            if ($dataItem[OptionTypeDescription::COLUMN_NAME_STORE_ID] == 0) {
                $defaultStoreDescription = $dataItem[OptionTypeDescription::COLUMN_NAME_DESCRIPTION];
            }
            if ($dataItem[OptionTypeDescription::COLUMN_NAME_STORE_ID] == $storeId) {
                $description = $dataItem[OptionTypeDescription::COLUMN_NAME_DESCRIPTION];
            }
        }
        $description = $description ?: $defaultStoreDescription;
        return [$this->getName() => htmlspecialchars_decode($description)];
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
                OptionTypeDescription::COLUMN_NAME_STORE_ID,
                OptionTypeDescription::COLUMN_NAME_DESCRIPTION
            ]
        )->where(
            OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                OptionTypeDescription::COLUMN_NAME_OPTION_TYPE_ID,
                OptionTypeDescription::COLUMN_NAME_STORE_ID,
                OptionTypeDescription::COLUMN_NAME_DESCRIPTION
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        $descriptions = [];
        if (empty($data['description'])) {
            return '';
        }
        if (is_array($data['description'])) {
            foreach ($data['description'] as $datum) {
                $descriptions[] = [
                    OptionTypeDescription::COLUMN_NAME_STORE_ID    => $datum['store_id'],
                    OptionTypeDescription::COLUMN_NAME_DESCRIPTION => $datum['description']
                ];
            }
        } else {
            $descriptions[] = [
                OptionTypeDescription::COLUMN_NAME_STORE_ID    => Store::DEFAULT_STORE_ID,
                OptionTypeDescription::COLUMN_NAME_DESCRIPTION => $data['description']
            ];
        }
        return json_encode($descriptions);
    }
}
