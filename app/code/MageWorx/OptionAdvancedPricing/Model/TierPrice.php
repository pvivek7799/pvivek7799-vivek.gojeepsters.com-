<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model;

use Magento\Catalog\Model\Product\Option\Value as OptionValue;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\CustomerVisibility as CustomerVisibilityHelper;
use MageWorx\OptionAdvancedPricing\Model\SpecialPrice as SpecialPriceModel;
use MageWorx\OptionAdvancedPricing\Model\ConditionValidator;

class TierPrice extends AbstractModel
{
    const TABLE_NAME                 = 'mageworx_optionadvancedpricing_option_type_tier_price';
    const OPTIONTEMPLATES_TABLE_NAME = 'mageworx_optiontemplates_group_option_type_tier_price';

    const COLUMN_OPTION_TYPE_TIER_PRICE_ID = 'option_type_tier_id';
    const COLUMN_MAGEWORX_OPTION_TYPE_ID   = 'mageworx_option_type_id';
    const COLUMN_OPTION_TYPE_ID            = 'option_type_id';
    const COLUMN_CUSTOMER_GROUP_ID         = 'customer_group_id';
    const COLUMN_QTY                       = 'qty';
    const COLUMN_PRICE                     = 'price';
    const COLUMN_PRICE_TYPE                = 'price_type';
    const COLUMN_DATE_FROM                 = 'date_from';
    const COLUMN_DATE_TO                   = 'date_to';

    const FIELD_OPTION_TYPE_ID_ALIAS = 'mageworx_tier_price_option_type_id';
    const KEY_TIER_PRICE             = 'tier_price';

    /**
     * @var CustomerVisibilityHelper
     */
    protected $customerVisibilityHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SpecialPriceModel
     */
    protected $specialPriceModel;

    /**
     * @var ConditionValidator
     */
    protected $conditionValidator;

    /**
     * TierPrice constructor.
     *
     * @param SpecialPriceModel $specialPriceModel
     * @param Helper $helper
     * @param CustomerVisibilityHelper $customerVisibilityHelper
     * @param ConditionValidator $conditionValidator
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SpecialPriceModel $specialPriceModel,
        Helper $helper,
        ConditionValidator $conditionValidator,
        CustomerVisibilityHelper $customerVisibilityHelper,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->specialPriceModel        = $specialPriceModel;
        $this->customerVisibilityHelper = $customerVisibilityHelper;
        $this->helper                   = $helper;
        $this->conditionValidator       = $conditionValidator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\OptionAdvancedPricing\Model\ResourceModel\TierPrice');
        $this->setIdFieldName(self::COLUMN_OPTION_TYPE_TIER_PRICE_ID);
    }

    /**
     * Get tier prices suitable by date and customer group
     *
     * @param OptionValue $optionValue
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSuitableTierPrices(OptionValue $optionValue)
    {
        $preparedData   = [];
        $tierPricesJson = $optionValue->getData(static::KEY_TIER_PRICE);
        if (!$tierPricesJson) {
            return $preparedData;
        }
        $tierPrices = json_decode($tierPricesJson, true);
        if (!$tierPrices) {
            return $preparedData;
        }

        $actualSpecialPrice = $this->specialPriceModel->getActualSpecialPrice($optionValue);
        if (!is_null($actualSpecialPrice) && $actualSpecialPrice < $optionValue->getPrice(true)) {
            $actualPrice = $actualSpecialPrice;
        } else {
            $actualPrice = $optionValue->getPrice(true);
        }

        $currentCustomer = $this->customerVisibilityHelper->getCurrentCustomerGroupId();
        foreach ($tierPrices as $tierPriceItem) {
            if ($tierPriceItem['price_type'] == Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT) {
                $tierPriceItem['price']      = $this->helper->getCalculatedPriceWithPercentageDiscount(
                    $optionValue,
                    $tierPriceItem
                );
                $tierPriceItem['price_type'] = Helper::PRICE_TYPE_FIXED;
            }

            if (!$this->conditionValidator->isValidated($tierPriceItem, $actualPrice)) {
                continue;
            }

            $tierPriceItem['percent'] = 100 - round($tierPriceItem['price'] / $actualPrice * 100);
            if ($tierPriceItem['customer_group_id'] == $currentCustomer
                || ($tierPriceItem['customer_group_id'] == $this->customerVisibilityHelper->getAllCustomersGroupId()
                    && empty($preparedData[$tierPriceItem['qty']]))
            ) {
                $preparedData[$tierPriceItem['qty']] = $tierPriceItem;
            }

        }
        return $preparedData;
    }
}
