<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use MageWorx\OptionSkuPolicy\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionFeatures\Helper\Data as HelperFeatures;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Request\Http as Request;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class SkuPolicy
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var HelperFeatures
     */
    protected $helperFeatures;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ProductInterface
     */
    protected $originalProduct;

    /**
     * @var ProductInterface
     */
    protected $quoteProduct;

    /**
     * @var bool
     */
    protected $isItemChanged;

    /**
     * @var bool
     */
    protected $isItemRemoved;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $buyRequest;

    /**
     * @var bool
     */
    protected $isGroupedSkuPolicyOnly;

    /**
     * @var string
     */
    protected $productSkuPolicy;

    /**
     * @var bool
     */
    protected $toCart;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;

    /**
     * @var array
     */
    protected $skuArray;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $newQuoteItems = [];

    /**
     * @var Configurable
     */
    protected $configurableEntity;


    /**
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param HelperFeatures $helperFeatures
     * @param DataObjectFactory $dataObjectFactory
     * @param Request $request
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableEntity
     */
    public function __construct(
        Helper $helper,
        BaseHelper $baseHelper,
        HelperFeatures $helperFeatures,
        DataObjectFactory $dataObjectFactory,
        Request $request,
        Configurable $configurableEntity,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper             = $helper;
        $this->baseHelper         = $baseHelper;
        $this->helperFeatures     = $helperFeatures;
        $this->dataObjectFactory  = $dataObjectFactory;
        $this->productRepository  = $productRepository;
        $this->request            = $request;
        $this->configurableEntity = $configurableEntity;
    }

    /**
     * Apply SKU policy to shopping cart
     *
     * @param \Magento\Quote\Model\Quote\Item[] $quoteItems
     * @return void
     */
    public function applySkuPolicyToCart($quoteItems)
    {
        $this->toCart = true;
        $this->applySkuPolicy($quoteItems);
    }

    /**
     * Apply SKU policy to order
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return void
     */
    public function applySkuPolicyToOrder($quote, $shippingAssignment)
    {
        if (!$quote->getCanApplySkuPolicyToOrder()) {
            return;
        }

        $quoteItems = $quote->getAllItems();
        if ($this->out($quoteItems, $shippingAssignment)) {
            return;
        }

        $this->toCart = false;
        $this->applySkuPolicy($quoteItems);
        $shippingAssignment->setItems($this->newQuoteItems);
        $this->newQuoteItems = [];
    }

    /**
     * Apply SKU policy to quote items
     *
     * @param \Magento\Quote\Model\Quote\Item[] $quoteItems
     * @return void
     */
    protected function applySkuPolicy($quoteItems)
    {
        $defaultSkuPolicy = $this->helper->getDefaultSkuPolicy();

        $quote = null;
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getIsSkuPolicyApplied() || $quoteItem->getQuote()->getIsProcessingOptions()) {
                continue;
            }

            $originalProduct = $this->productRepository->getById($quoteItem->getProductId());
            $quoteProduct    = $quoteItem->getProduct();
            if (!$this->hasOptions($originalProduct, $quoteProduct)) {
                $this->newQuoteItems[] = $quoteItem;
                continue;
            }

            $this->originalProduct = $originalProduct;
            $this->quoteProduct    = $quoteProduct;
            $this->quoteItem       = $quoteItem;
            $this->buyRequest      = $this->quoteItem->getBuyRequest();
            $this->isItemChanged   = false;
            $this->isItemRemoved   = false;
            $this->skuArray        = [];

            $this->addProductSku();

            if (!$quote) {
                $quote = $quoteItem->getQuote();
            }

            $this->productSkuPolicy = $this->quoteProduct->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG
                ? $defaultSkuPolicy
                : $this->quoteProduct->getSkuPolicy();

            /** @var array $options */
            $options = $this->buyRequest->getOptions();
            if (!$options) {
                continue;
            }

            $this->checkSkuPolicyGroupOnly($options);
            $this->processBuyRequestOptions($options);
            $this->addCustomSkuToBuyRequest();
            $this->saveNewQuoteItemOptions($quote);
            $this->modifyQuoteItem();

            $this->quoteItem->setSku(implode('-', $this->skuArray));
            $this->quoteItem->setIsSkuPolicyApplied(true);
            if (!$this->isItemRemoved) {
                $this->newQuoteItems[] = $this->quoteItem;
            }
        }

        $this->processQuote($quote);
    }

    /**
     * Add product SKU to result SKU array from original product (from child product for configurable)
     *
     * @return void
     */
    protected function addProductSku()
    {
        if ($this->quoteItem->getProductType() == 'configurable') {
            $superAttributes  = $this->buyRequest->getSuperAttribute();
            $childProduct     = $this->configurableEntity->getProductByAttributes(
                $superAttributes,
                $this->originalProduct
            );
            $this->skuArray[] = $childProduct->getSku();
        } else {
            $this->skuArray[] = $this->originalProduct->getSku();
        }
        return;
    }

    /**
     * Recollect totals and save quote and items after changes
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    protected function processQuote($quote)
    {
        if ($quote) {
            if ($quote->getTotalsCollectedFlag() == true) {
                $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            } else {
                $quote->save();
            }
        }
    }

    /**
     * Add custom SKU to buyRequest
     *
     * @return void
     */
    protected function addCustomSkuToBuyRequest()
    {
        $infoBuyRequest = $this->quoteItem->getOptionByCode('info_buyRequest');
        $this->buyRequest->setData('sku_policy_sku', implode('-', $this->skuArray));
        $infoBuyRequest->setValue($this->baseHelper->encodeBuyRequestValue($this->buyRequest->getData()));
        $this->quoteItem->addOption($infoBuyRequest);
    }

    /**
     * Save new/modified quote item's options for correct recollecting totals
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    protected function saveNewQuoteItemOptions($quote)
    {
        $quote->save();
    }

    /**
     * Check if there is only group SKU Policy on options
     *
     * @param array $options
     * @return void
     */
    protected function checkSkuPolicyGroupOnly($options)
    {
        foreach ($options as $optionId => $values) {
            $option = $this->quoteProduct->getOptionById($optionId);
            if (!$option) {
                continue;
            }
            $option->setProduct($this->quoteProduct);
            $skuPolicy = $option->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG
                ? $this->productSkuPolicy
                : $option->getSkuPolicy();
            if ($skuPolicy != Helper::SKU_POLICY_GROUPED) {
                $this->isGroupedSkuPolicyOnly = false;
                return;
            }
        }
        $this->isGroupedSkuPolicyOnly = true;
    }

    /**
     * Process buy request options to apply SKU policy
     *
     * @param array $options
     * @return void
     */
    protected function processBuyRequestOptions($options)
    {
        $this->quoteItem->getQuote()->setIsProcessingOptions(true);
        foreach ($options as $optionId => $values) {
            $option = $this->quoteProduct->getOptionById($optionId);
            if (!$option) {
                continue;
            }
            $option->setProduct($this->quoteProduct);

            if (in_array($option->getType(), $this->baseHelper->getSelectableOptionTypes())) {
                $this->processSelectableOption($option, $optionId, $values);
            } else {
                $this->processNonSelectableOption($option, $optionId, $values);
            }
        }
        $this->quoteItem->getQuote()->setIsProcessingOptions(false);
    }

    /**
     * Process selectable option
     *
     * @param ProductOptionInterface $option
     * @param int $optionId
     * @param array $values
     * @return void
     */
    protected function processSelectableOption($option, $optionId, $values)
    {
        if (is_array($values)) {
            $optionTypeIds = $values;
        } else {
            $optionTypeIds = explode(',', $values);
        }
        $isOneTime = $option->getOneTime();
        $skuPolicy = $option->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG
            ? $this->productSkuPolicy
            : $option->getSkuPolicy();

        $replacementSkus = [];
        foreach ($optionTypeIds as $index => $optionTypeId) {
            if (!$optionTypeId) {
                continue;
            }
            $value = $option->getValueById($optionTypeId);
            $sku   = $value->getSku();
            if (!$sku) {
                continue;
            }
            $replacementSkus[] = $sku;

            if ($skuPolicy == Helper::SKU_POLICY_STANDARD) {
                $this->skuArray[] = $sku;
            } elseif ($skuPolicy == Helper::SKU_POLICY_REPLACEMENT) {
                $this->skuArray[0] = implode('-', $replacementSkus);
            } elseif ($skuPolicy == Helper::SKU_POLICY_GROUPED
                || $skuPolicy == Helper::SKU_POLICY_INDEPENDENT
            ) {
                try {
                    $excludedItemCandidate = $this->productRepository->get($sku);
                } catch (NoSuchEntityException $e) {
                    $this->skuArray[] = $sku;
                    continue;
                }
                if (!$this->isExcludedItemValid($excludedItemCandidate)) {
                    $this->skuArray[] = $sku;
                    continue;
                }

                $optionQty      = $this->getOptionQty($this->buyRequest, $optionId, $optionTypeId);
                $optionTotalQty = $isOneTime ? $optionQty : $optionQty * $this->quoteItem->getQty();

                $request = $this->dataObjectFactory->create()->setQty($optionTotalQty);

                if ($this->helperFeatures->isWeightEnabled()) {
                    $request->setSkuPolicyWeight($value->getWeight());
                }
                if ($this->helperFeatures->isCostEnabled()) {
                    $request->setSkuPolicyCost($value->getCost());
                }

                $excludedItem = $this->quoteItem->getQuote()->addProduct(
                    $this->productRepository->get($sku, false, $this->quoteItem->getStoreId()),
                    $request
                );
                if (!is_object($excludedItem)) {
                    continue;
                }
                $this->quoteItem->getQuote()->setIsSuperMode(true);
                $excludedItem->setCustomPrice($value->getPrice());
                $excludedItem->setOriginalCustomPrice($value->getPrice());
                $excludedItem->setIsSkuPolicyApplied(true);
                $this->newQuoteItems[] = $excludedItem;

                $this->removeOptionAndOptionValueFromItem(
                    $values,
                    $optionId,
                    $index
                );

                if (!$this->toCart) {
                    $this->removeOutdatedQuoteItemData();
                }

                $this->isItemChanged = true;
                if ($skuPolicy == Helper::SKU_POLICY_GROUPED && $this->isGroupedSkuPolicyOnly) {
                    $this->isItemRemoved = true;
                }
            }
        }
    }

    /**
     * Process non-selectable option
     *
     * @param ProductOptionInterface $option
     * @param int $optionId
     * @param array $values
     * @return bool
     */
    protected function processNonSelectableOption($option, $optionId, $values)
    {
        $sku = $option->getSku();
        if (!$this->isNonSelectableValuesValid($values, $sku)) {
            return false;
        }

        $isOneTime = $option->getOneTime();
        $skuPolicy = $option->getSkuPolicy() == Helper::SKU_POLICY_USE_CONFIG
            ? $this->productSkuPolicy
            : $option->getSkuPolicy();

        if ($skuPolicy == Helper::SKU_POLICY_STANDARD) {
            $this->skuArray[] = $sku;
        } elseif ($skuPolicy == Helper::SKU_POLICY_REPLACEMENT) {
            $this->skuArray[0] = $sku;
        } elseif ($skuPolicy == Helper::SKU_POLICY_GROUPED
            || $skuPolicy == Helper::SKU_POLICY_INDEPENDENT
        ) {
            try {
                $excludedItemCandidate = $this->productRepository->get($sku);
            } catch (NoSuchEntityException $e) {
                $this->skuArray[] = $sku;
                return false;
            }
            if (!$this->isExcludedItemValid($excludedItemCandidate)) {
                $this->skuArray[] = $sku;
                return false;
            }

            $optionTotalQty = $isOneTime ? 1 : $this->quoteItem->getQty();
            $request        = $this->dataObjectFactory->create()->setQty($optionTotalQty);

            $excludedItem = $this->quoteItem->getQuote()->addProduct(
                $this->productRepository->get($sku, false, $this->quoteItem->getStoreId()),
                $request
            );
            if (!is_object($excludedItem)) {
                return false;
            }
            $this->quoteItem->getQuote()->setIsSuperMode(true);
            $excludedItem->setCustomPrice($option->getPrice());
            $excludedItem->setOriginalCustomPrice($option->getPrice());
            $excludedItem->setIsSkuPolicyApplied(true);
            $this->newQuoteItems[] = $excludedItem;

            $this->removeOptionFromItem($optionId);
            if (!$this->toCart) {
                $this->removeOutdatedQuoteItemData();
            }

            $this->isItemChanged = true;
            if ($skuPolicy == Helper::SKU_POLICY_GROUPED && $this->isGroupedSkuPolicyOnly) {
                $this->isItemRemoved = true;
            }
        }
    }

    /**
     * Validate quote item excluded by independent/grouped mode
     *
     * @param Item $quoteItem
     * @return bool
     */
    protected function isExcludedItemValid($quoteItem)
    {
        if (!in_array($quoteItem->getTypeId(), ['simple', 'virtual', 'downloadable'])) {
            return false;
        }

        if ($quoteItem->getRequiredOptions()) {
            return false;
        }
        return true;
    }

    /**
     * Validate non-selectable values
     *
     * @param array $values
     * @param string $sku
     * @return bool
     */
    protected function isNonSelectableValuesValid($values, $sku)
    {
        if (!$values || !$sku) {
            return false;
        }
        $isValuesEmpty = true;
        if (is_array($values)) {
            foreach ($values as $value) {
                if ($value) {
                    $isValuesEmpty = false;
                }
            }
        } else {
            $isValuesEmpty = false;
        }
        return !$isValuesEmpty;
    }

    /**
     * Modify quote item:
     * Increase qty if it is changed
     * Delete from quote items collection if it is removed
     *
     * @return void
     */
    protected function modifyQuoteItem()
    {
        if ($this->isItemRemoved) {
            $itemsCollection = $this->quoteItem->getQuote()->getItemsCollection();
            foreach ($itemsCollection as $key => $collectionItem) {
                if ($collectionItem === $this->quoteItem) {
                    $this->quoteItem->isDeleted(true);
                    $this->quoteItem->save();
                    $itemsCollection->removeItemByKey($key);
                }
            }
        } elseif ($this->isItemChanged) {
            $itemsCollection     = $this->quoteItem->getQuote()->getItemsCollection();
            $this->isItemRemoved = false;
            foreach ($itemsCollection as $key => $collectionItem) {
                if ($collectionItem->getProductId() == $this->quoteItem->getProductId()
                    && $collectionItem !== $this->quoteItem
                ) {
                    $currentOptions = !empty($this->buyRequest['options']) ? $this->buyRequest['options'] : false;

                    $collectionProduct = $collectionItem->getProduct();
                    $collectionOptions = false;
                    if ($collectionProduct->getHasOptions()) {
                        $buyRequest = $this->baseHelper->getInfoBuyRequest($collectionProduct);
                        if (!empty($buyRequest['options'])) {
                            $collectionOptions = $buyRequest['options'];
                        }
                    }

                    // compare options
                    if ($collectionOptions === $currentOptions) {
                        if (!$this->isUpdateCartItemAction()) {
                            $collectionItem->setQty($collectionItem->getQty() + $this->quoteItem->getQty());
                        } else {
                            $collectionItem->setQty($this->quoteItem->getQty());
                        }
                        $this->isItemRemoved = true;
                    }
                }
                if ($this->isItemRemoved && $collectionItem === $this->quoteItem && !$this->isUpdateCartItemAction()) {
                    $this->quoteItem->isDeleted(true);
                    $this->quoteItem->save();
                    $itemsCollection->removeItemByKey($key);
                }
            }
        }
    }

    /**
     * Check if it is update cart item action
     *
     * @return bool
     */
    protected function isUpdateCartItemAction()
    {
        if ($this->request->getModuleName() == 'checkout'
            && $this->request->getControllerName() == 'cart'
            && $this->request->getActionName() == 'updateItemOptions'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Clean quote item data to recollect totals
     * Used in applySkuPolicyToOrder only case
     *
     * @return void
     */
    protected function removeOutdatedQuoteItemData()
    {
        $requiredKeys = ['store_id', 'item_id', 'quote_id', 'product_id', 'product_type', 'sku', 'name', 'qty'];
        foreach ($this->quoteItem->getData() as $key => $value) {
            if (in_array($key, $requiredKeys)) {
                continue;
            }
            $this->quoteItem->unsetData($key);
        }
        return;
    }

    /**
     * Get option's qty
     *
     * @param array $post
     * @param int $optionId
     * @param int $optionTypeId
     * @return int
     */
    protected function getOptionQty($post, $optionId, $optionTypeId)
    {
        if (isset($post['options_' . $optionId . '_qty'])) {
            $optionQty = intval($post['options_' . $optionId . '_qty']);
        } elseif (isset($post['options_' . $optionId . '_' . $optionTypeId . '_qty'])) {
            $optionQty = intval($post['options_' . $optionId . '_' . $optionTypeId . '_qty']);
        } else {
            $optionQty = 1;
        }

        return $optionQty;
    }

    /**
     * Remove option and option value from quote item
     *
     * @param array|null $values
     * @param int $optionId
     * @param string $index
     * @return void
     */
    protected function removeOptionAndOptionValueFromItem(&$values, $optionId, $index)
    {
        if (is_array($values)) {
            unset($values[$index]);
        } else {
            $values = '';
        }
        if ($values) {
            $options            = $this->buyRequest->getData('options');
            $options[$optionId] = $values;
            $this->buyRequest->setData('options', $options);
            $itemOption = $this->quoteItem->getOptionByCode('option_' . $optionId);
            $itemOption->setValue(is_array($values) ? implode(',', $values) : $values);
            $this->quoteItem->addOption($itemOption);
        } else {
            $this->removeOptionFromItem($optionId);
        }
    }

    /**
     * Remove option from quote item
     *
     * @param int $optionId
     * @return void
     */
    protected function removeOptionFromItem($optionId)
    {
        $options = $this->buyRequest->getData('options');
        unset($options[$optionId]);
        $this->buyRequest->setData('options', $options);
        $this->quoteItem->removeOption('option_' . $optionId);

        $itemOptionIds = $this->quoteItem->getOptionByCode('option_ids');
        $optionIds     = $itemOptionIds->getValue();
        if ($optionIds) {
            $optionIds = explode(',', $optionIds);
            $i         = array_search($optionId, $optionIds);
            if ($i !== false) {
                unset($optionIds[$i]);
            }
            if ($optionIds) {
                $optionIds = implode(',', $optionIds);
            }
        }
        if ($optionIds) {
            $itemOptionIds->setValue($optionIds);
            $this->quoteItem->addOption($itemOptionIds);
        } else {
            $this->quoteItem->removeOption('option_ids');
        }
    }

    /**
     * Check conditions to start applying SKU policy
     *
     * @param array $items
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    protected function out($items, $shippingAssignment)
    {
        if (!$this->helper->isEnabledSkuPolicy()) {
            return true;
        }

        if (!$items) {
            return true;
        }

        if ($shippingAssignment->getShipping() &&
            $shippingAssignment->getShipping()->getAddress() &&
            $shippingAssignment->getShipping()->getAddress()->getAddressType() == 'billing') {
            return true;
        }

        return false;
    }

    /**
     * Check if original and quote product has options
     *
     * @param ProductInterface $originalProduct
     * @param ProductInterface $quoteProduct
     * @return bool
     */
    protected function hasOptions($originalProduct, $quoteProduct)
    {
        if (!$originalProduct || !$quoteProduct) {
            return false;
        }
        return $originalProduct->getHasOptions()
            && $quoteProduct->getHasOptions()
            && $originalProduct->getOptions()
            && $quoteProduct->getOptions();
    }
}