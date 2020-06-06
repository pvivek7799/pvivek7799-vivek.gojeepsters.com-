<?php
namespace Mexbs\MultiInventory\Model\Rewrite;

use \Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class StockRegistryProvider extends \Magento\CatalogInventory\Model\StockRegistryProvider
{
    protected $stockConfiguration;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockInterfaceFactory $stockFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemInterfaceFactory $stockItemFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusInterfaceFactory $stockStatusFactory,
        StockCriteriaInterfaceFactory $stockCriteriaFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct(
            $stockRepository,
            $stockFactory,
            $stockItemRepository,
            $stockItemFactory,
            $stockStatusRepository,
            $stockStatusFactory,
            $stockCriteriaFactory,
            $stockItemCriteriaFactory,
            $stockStatusCriteriaFactory
        );
    }

    /**
     * @return StockRegistryStorage
     */
    private function getStockRegistryStorage()
    {
        if (null === $this->stockRegistryStorage) {
            $this->stockRegistryStorage = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\StockRegistryStorage');
        }
        return $this->stockRegistryStorage;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId)
    {
        $stockItem = $this->getStockRegistryStorage()->getStockItem($productId, $scopeId);
        if (null === $stockItem) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setScopeFilter($scopeId);
            $criteria->setProductsFilter($productId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $this->getStockRegistryStorage()->setStockItem($productId, $scopeId, $stockItem);
            } else {
                $stockItem = $this->stockItemFactory->create();
            }
        }
        return $stockItem;
    }
}