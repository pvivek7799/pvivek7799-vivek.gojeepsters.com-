<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionFeatures\Model\OptionDescription;
use MageWorx\OptionFeatures\Model\ResourceModel\OptionDescription\Collection as DescriptionCollection;
use MageWorx\OptionFeatures\Model\OptionDescriptionFactory as DescriptionFactory;
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
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param DescriptionFactory $descriptionFactory
     * @param DescriptionCollection $descriptionCollection
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        DescriptionFactory $descriptionFactory,
        DescriptionCollection $descriptionCollection,
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
            'product' => OptionDescription::TABLE_NAME,
            'group'   => OptionDescription::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isOptionDescriptionEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $descriptions = [];
        foreach ($options as $option) {
            if (!isset($option['description'])) {
                continue;
            }
            $descriptions[$option['option_id']] = $option['description'];
        }

        return $this->collectDescriptions($descriptions);
    }

    /**
     * Save descriptions
     *
     * @param array $items
     * @return array
     */
    protected function collectDescriptions($items)
    {
        $data = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                OptionDescription::COLUMN_NAME_OPTION_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }
            foreach ($decodedJsonData as $dataItem) {
                $description = str_replace(PHP_EOL, '', $dataItem[OptionDescription::COLUMN_NAME_DESCRIPTION]);
                $description = str_replace('\\', '', $description);
                if ($description === '') {
                    continue;
                }
                $data['save'][] = [
                    OptionDescription::COLUMN_NAME_OPTION_ID => $itemKey,
                    OptionDescription::COLUMN_NAME_STORE_ID           =>
                        $dataItem[OptionDescription::COLUMN_NAME_STORE_ID],
                    OptionDescription::COLUMN_NAME_DESCRIPTION        =>
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
     * Delete old option description
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionIds = [];
        foreach ($data as $dataItem) {
            $optionIds[] = $dataItem[OptionDescription::COLUMN_NAME_OPTION_ID];
        }
        if (!$optionIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionDescription::COLUMN_NAME_OPTION_ID .
            " IN (" . "'" . implode("','", $optionIds) . "'" . ")";
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
        $storeId         = $this->systemHelper->resolveCurrentStoreId();
        $decodedJsonData = json_decode($object->getData($this->getName()), true);
        if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
            return [$this->getName() => ''];
        }
        $description             = '';
        $defaultStoreDescription = '';
        foreach ($decodedJsonData as $dataItem) {
            if ($dataItem[OptionDescription::COLUMN_NAME_STORE_ID] == 0) {
                $defaultStoreDescription = $dataItem[OptionDescription::COLUMN_NAME_DESCRIPTION];
            }
            if ($dataItem[OptionDescription::COLUMN_NAME_STORE_ID] == $storeId) {
                $description = $dataItem[OptionDescription::COLUMN_NAME_DESCRIPTION];
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
        $table      = $this->resource->getTableName($this->getTableName($entityType));

        $select = $connection->select()->from(
            $table,
            [
                new \Zend_Db_Expr($newId),
                OptionDescription::COLUMN_NAME_STORE_ID,
                OptionDescription::COLUMN_NAME_DESCRIPTION
            ]
        )->where(
            OptionDescription::COLUMN_NAME_OPTION_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                OptionDescription::COLUMN_NAME_OPTION_ID,
                OptionDescription::COLUMN_NAME_STORE_ID,
                OptionDescription::COLUMN_NAME_DESCRIPTION
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
                    OptionDescription::COLUMN_NAME_STORE_ID    => $datum['store_id'],
                    OptionDescription::COLUMN_NAME_DESCRIPTION => $datum['description']
                ];
            }
        } else {
            $descriptions[] = [
                OptionDescription::COLUMN_NAME_STORE_ID    => Store::DEFAULT_STORE_ID,
                OptionDescription::COLUMN_NAME_DESCRIPTION => $data['description']
            ];
        }
        return json_encode($descriptions);
    }
}
