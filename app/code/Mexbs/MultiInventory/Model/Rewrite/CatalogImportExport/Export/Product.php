<?php
namespace Mexbs\MultiInventory\Model\Rewrite\CatalogImportExport\Export;

use Magento\ImportExport\Model\Import;
use \Magento\Store\Model\Store;
use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Product extends \Magento\CatalogImportExport\Model\Export\Product{

    protected $storeListFactory;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\CatalogImportExport\Model\Export\Product\Type\Factory $_typeFactory,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface $rowCustomizer,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeListFactory
    ) {
        $this->storeListFactory = $storeListFactory;

        parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager,
            $logger,
            $collectionFactory,
            $exportConfig,
            $productFactory,
            $attrSetColFactory,
            $categoryColFactory,
            $itemFactory,
            $optionColFactory,
            $attributeColFactory,
            $_typeFactory,
            $linkTypeProvider,
            $rowCustomizer
        );
    }

    /**
     * Retrieve new (not loaded) Store collection object with website filter
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($websiteId)
    {
        return $this->storeListFactory->create()->addWebsiteFilter($websiteId)->setLoadDefault(true);
    }

    /**
     * gets one of the store ids of the website
     */
    protected function getArbitraryStoreIdOfWebsite($websiteId){
        return $this->getStoreCollection($websiteId)->getFirstItem()->getId();
    }

    protected function prepareCatalogInventory(array $productIdsToPrepare)
    {

        if (empty($productIdsToPrepare)) {
            return [];
        }
        $prepareSelect = $this->_connection->select()->from(
            $this->_itemFactory->create()->getMainTable()
        )->where(
                'product_id IN (?)',
                $productIdsToPrepare
            );

        $prepareStmt = $this->_connection->query($prepareSelect);
        $stockItemRows = [];
        while ($stockItemRowToPrepare = $prepareStmt->fetch()) {
            $productId = $stockItemRowToPrepare['product_id'];
            unset(
            $stockItemRowToPrepare['item_id'],
            $stockItemRowToPrepare['product_id'],
            $stockItemRowToPrepare['low_stock_date'],
            $stockItemRowToPrepare['stock_id'],
            $stockItemRowToPrepare['stock_status_changed_auto']
            );
            $arbitraryStoreIdOfWebsite = $this->getArbitraryStoreIdOfWebsite($stockItemRowToPrepare['website_id']);
            if($arbitraryStoreIdOfWebsite !== null){
                $stockItemRows[$productId][$arbitraryStoreIdOfWebsite] = $stockItemRowToPrepare;
            }
        }
        return $stockItemRows;
    }

    private function appendMultirowData(&$dataRowToAppend, &$multiRawDataToAppendTo)
    {
        $productId = $dataRowToAppend['product_id'];
        $productLinkId = $dataRowToAppend['product_link_id'];
        $storeIdToAppend = $dataRowToAppend['store_id'];
        $sku = $dataRowToAppend[self::COL_SKU];

        unset($dataRowToAppend['product_id']);
        unset($dataRowToAppend['product_link_id']);
        unset($dataRowToAppend['store_id']);
        unset($dataRowToAppend[self::COL_SKU]);

        if (Store::DEFAULT_STORE_ID == $storeIdToAppend) {
            unset($dataRowToAppend[self::COL_STORE]);
            $this->updateDataWithCategoryColumns($dataRowToAppend, $multiRawDataToAppendTo['rowCategories'], $productId);
            if (!empty($multiRawDataToAppendTo['rowWebsites'][$productId])) {
                $websiteCodes = [];
                foreach ($multiRawDataToAppendTo['rowWebsites'][$productId] as $productWebsite) {
                    $websiteCodes[] = $this->_websiteIdToCode[$productWebsite];
                }
                $dataRowToAppend[self::COL_PRODUCT_WEBSITES] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $websiteCodes);
                $multiRawDataToAppendTo['rowWebsites'][$productId] = [];
            }
            if (!empty($multiRawDataToAppendTo['mediaGalery'][$productLinkId])) {
                $extra = [];
                $extraImageLabels = [];
                $extraImageIsDisabled = [];
                foreach ($multiRawDataToAppendTo['mediaGalery'][$productLinkId] as $mediaItem) {
                    $extraImages[] = $mediaItem['_media_image'];
                    $extraImageLabels[] = $mediaItem['_media_label'];

                    if ($mediaItem['_media_is_disabled'] == true) {
                        $extraImageIsDisabled[] = $mediaItem['_media_image'];
                    }
                }
                $dataRowToAppend['additional_images'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $extraImages);
                $dataRowToAppend['additional_image_labels'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $extraImageLabels);
                $dataRowToAppend['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $extraImageIsDisabled);
                $multiRawDataToAppendTo['mediaGalery'][$productLinkId] = [];
            }
            foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkId) {
                if (!empty($multiRawDataToAppendTo['linksRows'][$productLinkId][$linkId])) {
                    $colPrefix = $linkTypeName . '_';

                    $linkAssociations = [];
                    foreach ($multiRawDataToAppendTo['linksRows'][$productLinkId][$linkId] as $linkData) {
                        if ($linkData['default_qty'] !== null) {
                            $skuItem = $linkData['sku'] . ImportProduct::PAIR_NAME_VALUE_SEPARATOR .
                                $linkData['default_qty'];
                        } else {
                            $skuItem = $linkData['sku'];
                        }
                        $linkAssociations[$skuItem] = $linkData['position'];
                    }
                    $multiRawDataToAppendTo['linksRows'][$productLinkId][$linkId] = [];
                    asort($linkAssociations);
                    $dataRowToAppend[$colPrefix . 'skus'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_keys($linkAssociations));
                    $dataRowToAppend[$colPrefix . 'position'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_values($linkAssociations));
                }
            }
            $dataRowToAppend = $this->rowCustomizer->addData($dataRowToAppend, $productId);
        }

        if (!empty($this->collectedMultiselectsData[$storeIdToAppend][$productId])) {
            foreach (array_keys($this->collectedMultiselectsData[$storeIdToAppend][$productId]) as $attrKey) {
                if (!empty($this->collectedMultiselectsData[$storeIdToAppend][$productId][$attrKey])) {
                    $dataRowToAppend[$attrKey] = implode(
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        $this->collectedMultiselectsData[$storeIdToAppend][$productId][$attrKey]
                    );
                }
            }
        }

        if (!empty($multiRawDataToAppendTo['customOptionsData'][$productLinkId][$storeIdToAppend])) {
            $customOptionsRows = $multiRawDataToAppendTo['customOptionsData'][$productLinkId][$storeIdToAppend];
            $multiRawDataToAppendTo['customOptionsData'][$productLinkId][$storeIdToAppend] = [];
            $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $customOptionsRows);

            $dataRowToAppend = array_merge($dataRowToAppend, ['custom_options' => $customOptions]);
        }

        if (empty($dataRowToAppend)) {
            return null;
        } elseif ($storeIdToAppend != Store::DEFAULT_STORE_ID) {
            $dataRowToAppend[self::COL_STORE] = $this->_storeIdToCode[$storeIdToAppend];
        }
        $dataRowToAppend[self::COL_SKU] = $sku;
        return $dataRowToAppend;
    }

    protected function setHeaderColumns($customOptionsDataForHeader, $stockItemRows)
    {
        if (!$this->_headerColumns) {
            $lastStockItemRowsElement = end($stockItemRows);
            $lastStockItemRowsSubElement = (!empty($lastStockItemRowsElement) ? array_keys(end($lastStockItemRowsElement)) : []);

            $customOptCols = [
                'custom_options',
            ];

            $exportMainAttrCodes = $this->_getExportMainAttrCodes();
            $this->_headerColumns = array_merge(
                [
                    self::COL_SKU,
                    self::COL_STORE,
                    self::COL_ATTR_SET,
                    self::COL_TYPE,
                    self::COL_CATEGORY,
                    self::COL_PRODUCT_WEBSITES,
                ],
                $exportMainAttrCodes,
                [self::COL_ADDITIONAL_ATTRIBUTES],
                $lastStockItemRowsSubElement,
                [],
                [
                    'related_skus',
                    'related_position',
                    'crosssell_skus',
                    'crosssell_position',
                    'upsell_skus',
                    'upsell_position'
                ],
                ['additional_images', 'additional_image_labels', 'hide_from_product_page']
            );
            // have we merge custom options columns
            if ($customOptionsDataForHeader) {
                $this->_headerColumns = array_merge($this->_headerColumns, $customOptCols);
            }
        }
    }

    protected function getExportData()
    {
        $exportData = [];
        try {
            $rawDataToExport = $this->collectRawData();
            $multirawDataToExport = $this->collectMultirawData();

            $productIds = array_keys($rawDataToExport);
            $stockItemRowsToExport = $this->prepareCatalogInventory($productIds);

            $this->rowCustomizer->prepareData($this->_getEntityCollection(), $productIds);

            $this->setHeaderColumns($multirawDataToExport['customOptionsData'], $stockItemRowsToExport);
            $this->_headerColumns = $this->rowCustomizer->addHeaderColumns($this->_headerColumns);

            foreach ($rawDataToExport as $productId => $productData) {
                foreach ($productData as $storeId => $dataRow) {
                    if (isset($stockItemRowsToExport[$productId][$storeId])) {
                        $dataRow = array_merge($dataRow, $stockItemRowsToExport[$productId][$storeId]);
                        $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
                        $dataRow[self::COL_ATTR_SET] = (
                            isset($productData[Store::DEFAULT_STORE_ID][self::COL_ATTR_SET]) ?
                                $productData[Store::DEFAULT_STORE_ID][self::COL_ATTR_SET] :
                            ''
                        );
                        $dataRow[self::COL_TYPE] = (
                        isset($productData[Store::DEFAULT_STORE_ID][self::COL_TYPE]) ?
                            $productData[Store::DEFAULT_STORE_ID][self::COL_TYPE] :
                            ''
                        );
                    }
                    $this->appendMultirowData($dataRow, $multirawDataToExport);
                    if ($dataRow) {
                        $exportData[] = $dataRow;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }
}