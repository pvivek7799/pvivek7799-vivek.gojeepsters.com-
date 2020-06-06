<?php
namespace Mexbs\MultiInventory\Observer\Rewrite;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddStockItemsObserver extends \Magento\CatalogInventory\Observer\AddStockItemsObserver
{
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $criteriaInterfaceFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    protected $storeManager;

    /**
     * AddStockItemsObserver constructor.
     *
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct(
            $criteriaInterfaceFactory,
            $stockItemRepository,
            $stockConfiguration
        );
    }

    public function execute(Observer $observer)
    {
        /** @var Collection $productCollection */
        $productCollection = $observer->getData('collection');
        $productIds = array_keys($productCollection->getItems());
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setProductsFilter($productIds);

        $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();
        $scopeId = $defaultScopeId;
        if($productCollection->getStoreId()){
            $scopeId = $this->storeManager->getStore($productCollection->getStoreId())->getWebsiteId();
        }

        $criteria->setScopeFilter($scopeId);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        foreach ($stockItemCollection->getItems() as $item) {
            /** @var Product $product */
            $product = $productCollection->getItemById($item->getProductId());
            $productExtension = $product->getExtensionAttributes();
            $productExtension->setStockItem($item);
            $product->setExtensionAttributes($productExtension);
        }
    }
}