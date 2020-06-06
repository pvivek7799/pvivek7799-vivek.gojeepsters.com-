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
use MageWorx\OptionAdvancedPricing\Model\TierPrice as TierPriceModel;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class TierPrice extends AbstractAttribute implements AttributeInterface
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
     * @var TierPriceModel
     */
    protected $tierPriceModel;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param TierPriceModel $tierPriceModel
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        TierPriceModel $tierPriceModel,
        SystemHelper $systemHelper
    ) {
        $this->helper         = $helper;
        $this->tierPriceModel = $tierPriceModel;
        $this->systemHelper   = $systemHelper;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return TierPriceModel::KEY_TIER_PRICE;
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
            'product' => TierPriceModel::TABLE_NAME,
            'group'   => TierPriceModel::OPTIONTEMPLATES_TABLE_NAME
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
        if (!$this->helper->isTierPriceEnabled()) {
            return [];
        }

        $this->entity = $entity;

        $tierPrices = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $tierPrices[$value[TierPriceModel::COLUMN_OPTION_TYPE_ID]] = $value[$this->getName()];
            }
        }

        return $this->collectTierPrices($tierPrices);
    }

    /**
     * Collect tier prices
     *
     * @param array $items
     * @return array
     */
    protected function collectTierPrices($items)
    {
        $data = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                TierPriceModel::COLUMN_OPTION_TYPE_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }
            foreach ($decodedJsonData as $dataItem) {
                $dateFrom  = $dataItem[TierPriceModel::COLUMN_DATE_FROM] ?: null;
                $dateTo    = $dataItem[TierPriceModel::COLUMN_DATE_TO] ?: null;
                $price     = $dataItem[TierPriceModel::COLUMN_PRICE];
                $priceType = $dataItem[TierPriceModel::COLUMN_PRICE_TYPE];
                if ($priceType == Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT) {
                    $price = abs($price);
                }
                $data['save'][] = [
                    TierPriceModel::COLUMN_OPTION_TYPE_ID    => $itemKey,
                    TierPriceModel::COLUMN_CUSTOMER_GROUP_ID =>
                        (int)$dataItem[TierPriceModel::COLUMN_CUSTOMER_GROUP_ID],
                    TierPriceModel::COLUMN_PRICE             => $price,
                    TierPriceModel::COLUMN_PRICE_TYPE        => $priceType,
                    TierPriceModel::COLUMN_QTY               => $dataItem[TierPriceModel::COLUMN_QTY],
                    TierPriceModel::COLUMN_DATE_FROM         => $dateFrom,
                    TierPriceModel::COLUMN_DATE_TO           => $dateTo,
                ];
            }
        }
        return $data;
    }

    /**
     * Delete old option value tier prices
     *
     * @param $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[TierPriceModel::COLUMN_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = TierPriceModel::COLUMN_OPTION_TYPE_ID .
            " IN (" . implode(",", $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        $tierPrices = $this->tierPriceModel->getSuitableTierPrices($object);
        if (!$tierPrices) {
            return [];
        } else {
            return [$this->getName() => json_encode($tierPrices)];
        }
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
                TierPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                TierPriceModel::COLUMN_QTY,
                TierPriceModel::COLUMN_PRICE,
                TierPriceModel::COLUMN_PRICE_TYPE,
                TierPriceModel::COLUMN_DATE_FROM,
                TierPriceModel::COLUMN_DATE_TO
            ]
        )->where(
            TierPriceModel::COLUMN_OPTION_TYPE_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                TierPriceModel::COLUMN_OPTION_TYPE_ID,
                TierPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                TierPriceModel::COLUMN_QTY,
                TierPriceModel::COLUMN_PRICE,
                TierPriceModel::COLUMN_PRICE_TYPE,
                TierPriceModel::COLUMN_DATE_FROM,
                TierPriceModel::COLUMN_DATE_TO
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function validateTemplateImportMageOne($data)
    {
        if (!isset($data['tiers']) || !is_array($data['tiers'])) {
            return true;
        }

        foreach ($data['tiers'] as $tierPriceItem) {
            if (!isset($tierPriceItem['customer_group_id'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'customer_group_id')
                );
            }
            if (!isset($tierPriceItem['price'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'price')
                );
            }
            if (!isset($tierPriceItem['price_type'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'price_type')
                );
            }
            if (!isset($tierPriceItem['qty'])) {
                throw new LocalizedException(
                    __("Special price's field '%1' not found", 'qty')
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
        $tierPrices = [];
        if (!isset($data['tiers']) || !is_array($data['tiers'])) {
            return '';
        }

        foreach ($data['tiers'] as $tierPriceItem) {
            if ($tierPriceItem['price_type'] == 'percent') {
                $price = 100 - $tierPriceItem['price'];
                $priceType = Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT;
            } else {
                $price = $tierPriceItem['price'];
                $priceType = $tierPriceItem['price_type'];
            }
            $tierPrices[] = [
                TierPriceModel::COLUMN_CUSTOMER_GROUP_ID => $tierPriceItem['customer_group_id'],
                TierPriceModel::COLUMN_PRICE             => $price,
                TierPriceModel::COLUMN_PRICE_TYPE        => $priceType,
                TierPriceModel::COLUMN_QTY               => $tierPriceItem['qty'],
                TierPriceModel::COLUMN_DATE_FROM         => '',
                TierPriceModel::COLUMN_DATE_TO           => '',
            ];
        }

        return json_encode($tierPrices);
    }
}
