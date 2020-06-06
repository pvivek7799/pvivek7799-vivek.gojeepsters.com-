<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\Product;

class IndexerStockCacheCleaner
{
    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        CacheContext $cacheContext,
        ManagerInterface $eventManager
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    public function aroundAddInStockFilterToCollection(
        \Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner $subject,
        Closure $proceed,
        array $productIds, callable $reindex
    ){
        $proceed($productIds, $reindex);

        $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}