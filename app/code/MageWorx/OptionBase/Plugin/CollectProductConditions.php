<?php

/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin;

use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as OptionValueCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageWorx\OptionBase\Helper\System as SystemHelper;

class CollectProductConditions
{
    /**
     * @var CollectionUpdaterRegistry
     */
    private $collectionUpdaterRegistry;

    /**
     * @var OptionValueCollectionFactory
     */
    protected $optionValueCollectionFactory;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param OptionValueCollectionFactory $optionValueCollectionFactory
     * @param SystemHelper $systemHelper
     * @param OptionCollectionFactory $optionCollectionFactory
     * @param StoreManager $storeManager
     */
    public function __construct(
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        OptionValueCollectionFactory $optionValueCollectionFactory,
        SystemHelper $systemHelper,
        OptionCollectionFactory $optionCollectionFactory,
        StoreManager $storeManager
    ) {
        $this->collectionUpdaterRegistry    = $collectionUpdaterRegistry;
        $this->optionValueCollectionFactory = $optionValueCollectionFactory;
        $this->systemHelper                 = $systemHelper;
        $this->optionCollectionFactory      = $optionCollectionFactory;
        $this->storeManager                 = $storeManager;
    }

    /**
     * Adding product custom options to result collection
     *
     * @return $this
     */
    public function aroundAddOptionsToResult($subject, \Closure $proceed)
    {
        $productsByLinkId = [];

        foreach ($subject as $product) {
            $productId = $product->getData(
                $product->getResource()->getLinkField()
            );

            $productsByLinkId[$productId] = $product;
        }

        if (!empty($productsByLinkId)) {
            $this->collectionUpdaterRegistry->setOptionIds([]);
            $this->collectionUpdaterRegistry->setOptionValueIds([]);
            $this->collectionUpdaterRegistry->setCurrentEntityIds(array_keys($productsByLinkId));
            $this->collectionUpdaterRegistry->setCurrentRowIds(array_keys($productsByLinkId));

            $options = $this->optionCollectionFactory->create()->addTitleToResult(
                $this->storeManager->getStore()->getId()
            )->addPriceToResult(
                $this->storeManager->getStore()->getId()
            )->addProductToFilter(
                array_keys($productsByLinkId)
            )->addValuesToResult();

            foreach ($options as $option) {
                if (isset($productsByLinkId[$option->getProductId()])) {
                    $productsByLinkId[$option->getProductId()]->addOption($option);
                }
            }
        }

        return $subject;
    }
}
