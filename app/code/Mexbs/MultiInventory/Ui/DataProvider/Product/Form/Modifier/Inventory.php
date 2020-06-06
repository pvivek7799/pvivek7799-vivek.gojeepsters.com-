<?php
namespace Mexbs\MultiInventory\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Ui\Component\Form;

/**
 * Data provider for advanced inventory form
 */
class Inventory extends AbstractModifier
{
    const STOCK_DATA_FIELDS = 'stock_data';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    private $yesNoOptions;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param StockRegistryInterface $stockRegistry
     * @param ArrayManager $arrayManager
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        LocatorInterface $locator,
        StockRegistryInterface $stockRegistry,
        ArrayManager $arrayManager,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Config\Model\Config\Source\Yesno $yesNoOptions
    ) {
        $this->locator = $locator;
        $this->stockRegistry = $stockRegistry;
        $this->arrayManager = $arrayManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->yesNoOptions = $yesNoOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        $useDefaultValues = 0;
        if($product->getStoreId()){
            $stockItem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $useDefaultValues = 1;
            if($stockItem && $stockItem->getId()){
                $useDefaultValues = $stockItem->getUseDefaultValues();
            }
        }

        $data[$this->locator->getProduct()->getId()]['product']['stock_data']['use_default_values'] = $useDefaultValues;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->prepareMeta();

        return $this->meta;
    }

    private function prepareMeta()
    {
        $config['arguments']['data']['config'] = [
            'scopeLabel' => '[WEBSITE]',
            'globalScope' => false
        ];

        $containerConfig['arguments']['data']['config'] = [
            'scopeLabel' => '[WEBSITE]',
            'globalScope' => false
        ];

        $containerNodesNames = [
            'container_manage_stock',
            'container_min_qty',
            'container_min_sale_qty',
            'container_max_sale_qty',
            'container_backorders',
            'container_notify_stock_qty',
            'container_enable_qty_increments',
            'container_qty_increments',
            'container_is_in_stock',
        ];

        $nodeNames = [
            'container_manage_stock' => [
                'manage_stock'
            ],
            'qty' => [],
            'container_min_qty' => [
                'min_qty'
            ],
            'container_min_sale_qty' => [
                'min_sale_qty',
                'min_qty_allowed_in_shopping_cart',
            ],
            'container_max_sale_qty' => [
                'max_sale_qty'
            ],
            'is_qty_decimal' => [],
            'is_decimal_divided' => [],
            'container_backorders' => [
                'backorders'
            ],
            'container_notify_stock_qty' => [
                'notify_stock_qty'
            ],
            'container_enable_qty_increments' => [
                'enable_qty_increments'
            ],
            'container_qty_increments' => [
                'qty_increments'
            ],
            'container_is_in_stock' => [
                'is_in_stock'
            ]
        ];


        $configData['children']['stock_data']['children'] = [];
        foreach($nodeNames as $nodeName => $childrenNodeNames){
            if(in_array($nodeName, $containerNodesNames)){
                $configData['children']['stock_data']['children'][$nodeName] = $containerConfig;
            }else{
                $configData['children']['stock_data']['children'][$nodeName] = $config;
            }
            if(count($childrenNodeNames) > 0){
                foreach($childrenNodeNames as $childName){
                    $configData['children']['stock_data']['children'][$nodeName]['children'][$childName] = $config;
                }
            }
        }

        $this->meta = $this->arrayManager->merge(
            'advanced_inventory_modal',
            $this->meta,
            $configData
        );

        $product = $this->locator->getProduct();
        if($product->getStoreId()){
            $configData['children']['stock_data']['children']['use_default_values']['arguments']['data']['config'] = [
                'label' => 'Use Default Values',
                'formElement' => 'select',
                'componentType' => Form\Field::NAME,
                'rawOptions' => 'true',
                'dataScope' => 'stock_data.use_default_values',
                'value' => '0',
                'sortOrder' => '50',
                'scopeLabel' => '[WEBSITE]',
                'options' => $this->yesNoOptions->toOptionArray()
            ];

            $this->meta = $this->arrayManager->merge(
                'advanced_inventory_modal',
                $this->meta,
                $configData
            );
        }
    }
}
