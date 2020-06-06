<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model\Attribute\Option\Value;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionAdvancedPricing\Model\SpecialPrice as SpecialPriceModel;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class SpecialPrice extends AbstractAttribute implements AttributeInterface
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
     * @var SpecialPriceModel
     */
    protected $specialPriceModel;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param SystemHelper $systemHelper
     * @param SpecialPriceModel $specialPriceModel
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        SpecialPriceModel $specialPriceModel,
        SystemHelper $systemHelper
    ) {
        $this->helper            = $helper;
        $this->systemHelper      = $systemHelper;
        $this->specialPriceModel = $specialPriceModel;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return SpecialPriceModel::KEY_SPECIAL_PRICE;
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
            'product' => SpecialPriceModel::TABLE_NAME,
            'group'   => SpecialPriceModel::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isSpecialPriceEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $specialPrices = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $specialPrices[$value[SpecialPriceModel::COLUMN_OPTION_TYPE_ID]] = $value[$this->getName()];
            }
        }

        return $this->collectSpecialPrices($specialPrices);
    }

    /**
     * Collect special prices
     *
     * @param array $items
     * @return array
     */
    protected function collectSpecialPrices($items)
    {
        $data = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                SpecialPriceModel::COLUMN_OPTION_TYPE_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }
            foreach ($decodedJsonData as $dataItem) {
                $dateFrom  = $dataItem[SpecialPriceModel::COLUMN_DATE_FROM] ?: null;
                $dateTo    = $dataItem[SpecialPriceModel::COLUMN_DATE_TO] ?: null;
                $comment   = str_replace('\\', '', $dataItem[SpecialPriceModel::COLUMN_COMMENT]);
                $comment   = htmlspecialchars(
                    $comment,
                    ENT_COMPAT,
                    'UTF-8',
                    false
                );
                $price     = $dataItem[SpecialPriceModel::COLUMN_PRICE];
                $priceType = $dataItem[SpecialPriceModel::COLUMN_PRICE_TYPE];
                if ($priceType == Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT) {
                    $price = abs($price);
                }
                $data['save'][] = [
                    SpecialPriceModel::COLUMN_OPTION_TYPE_ID    => $itemKey,
                    SpecialPriceModel::COLUMN_CUSTOMER_GROUP_ID =>
                        (int)$dataItem[SpecialPriceModel::COLUMN_CUSTOMER_GROUP_ID],
                    SpecialPriceModel::COLUMN_PRICE             => $price,
                    SpecialPriceModel::COLUMN_PRICE_TYPE        => $priceType,
                    SpecialPriceModel::COLUMN_COMMENT           => $comment,
                    SpecialPriceModel::COLUMN_DATE_FROM         => $dateFrom,
                    SpecialPriceModel::COLUMN_DATE_TO           => $dateTo,
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
            $optionValueIds[] = $dataItem[SpecialPriceModel::COLUMN_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = SpecialPriceModel::COLUMN_OPTION_TYPE_ID .
            " IN (" . implode(",", $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [$this->getName() => $this->specialPriceModel->getActualSpecialPrice($object)];
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
                SpecialPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                SpecialPriceModel::COLUMN_PRICE,
                SpecialPriceModel::COLUMN_PRICE_TYPE,
                SpecialPriceModel::COLUMN_COMMENT,
                SpecialPriceModel::COLUMN_DATE_FROM,
                SpecialPriceModel::COLUMN_DATE_TO
            ]
        )->where(
            SpecialPriceModel::COLUMN_OPTION_TYPE_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                SpecialPriceModel::COLUMN_OPTION_TYPE_ID,
                SpecialPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                SpecialPriceModel::COLUMN_PRICE,
                SpecialPriceModel::COLUMN_PRICE_TYPE,
                SpecialPriceModel::COLUMN_COMMENT,
                SpecialPriceModel::COLUMN_DATE_FROM,
                SpecialPriceModel::COLUMN_DATE_TO
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function validateTemplateImportMageOne($data)
    {
        if (!isset($data['specials']) || !is_array($data['specials'])) {
            return true;
        }

        foreach ($data['specials'] as $specialPriceItem) {
            if (!isset($specialPriceItem['customer_group_id'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'customer_group_id')
                );
            }
            if (!isset($specialPriceItem['price'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'price')
                );
            }
            if (!isset($specialPriceItem['price_type'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'price_type')
                );
            }
            if (!isset($specialPriceItem['comment'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'comment')
                );
            }
            if (!isset($specialPriceItem['date_from'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'date_from')
                );
            }
            if (!isset($specialPriceItem['date_to'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'date_to')
                );
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        $specialPrices = [];
        if (!isset($data['specials']) || !is_array($data['specials'])) {
            return '';
        }

        foreach ($data['specials'] as $specialPriceItem) {
            if ($specialPriceItem['price_type'] == 'percent') {
                $price = 100 - $specialPriceItem['price'];
                $priceType = Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT;
            } else {
                $price = $specialPriceItem['price'];
                $priceType = $specialPriceItem['price_type'];
            }
            $specialPrices[] = [
                SpecialPriceModel::COLUMN_CUSTOMER_GROUP_ID => $specialPriceItem['customer_group_id'],
                SpecialPriceModel::COLUMN_PRICE             => $price,
                SpecialPriceModel::COLUMN_PRICE_TYPE        => $priceType,
                SpecialPriceModel::COLUMN_COMMENT           => $specialPriceItem['comment'],
                SpecialPriceModel::COLUMN_DATE_FROM         => $specialPriceItem['date_from'],
                SpecialPriceModel::COLUMN_DATE_TO           => $specialPriceItem['date_to'],
            ];
        }

        return json_encode($specialPrices);
    }
}
