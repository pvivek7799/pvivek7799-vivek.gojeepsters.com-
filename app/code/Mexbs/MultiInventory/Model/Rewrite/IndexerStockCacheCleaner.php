<?php
namespace Mexbs\MultiInventory\Model\Rewrite;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\Product;

class IndexerStockCacheCleaner extends \Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner
{
    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;


    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfiguration
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ResourceConnection $resource,
        StockConfigurationInterface $stockConfiguration,
        CacheContext $cacheContext,
        ManagerInterface $eventManager
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;

        parent::__construct(
            $resource,
            $stockConfiguration,
            $cacheContext,
            $eventManager
        );
    }

    public function clean(array $productIds, callable $reindex)
    {
        $reindex();
        $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}