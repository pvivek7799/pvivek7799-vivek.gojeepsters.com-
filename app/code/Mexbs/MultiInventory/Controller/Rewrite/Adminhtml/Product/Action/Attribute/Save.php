<?php
namespace Mexbs\MultiInventory\Controller\Rewrite\Adminhtml\Product\Action\Attribute;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action{
    protected $stockConfiguration;
    protected $stockIndexerProcessor;
    protected $dataObjectHelper;
    protected $productFlatIndexerProcessor;
    protected $catalogProduct;
    protected $productPriceIndexerProcessor;
    protected $productMetaData;
    protected $operationFactory;
    protected $serializer;
    protected $bulkManagement;
    protected $userContext;
    protected $stockRegistry;
    protected $stockItemRepository;
    protected $bulkSize;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operationFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        int $bulkSize = 100
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->catalogProduct = $catalogProduct;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productMetaData = $productMetaData;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
        $this->bulkManagement = $bulkManagement;
        $this->attributeHelper = $attributeHelper;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->bulkSize = $bulkSize;

        parent::__construct($context);
    }

    private function makeOperation(
        $meta,
        $queue,
        $dataToUpdate,
        $storeId,
        $websiteId,
        $productIds,
        $bulkUuid
    ) {
        $dataToEncode = [
            'meta_information' => $meta,
            'product_ids' => $productIds,
            'store_id' => $storeId,
            'website_id' => $websiteId,
            'attributes' => $dataToUpdate
        ];
        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => $queue,
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status' => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }

    private function publish(
        $attributesData,
        $websiteRemoveData,
        $websiteAddData,
        $storeId,
        $websiteId,
        $productIds
    ){
        $productIdsChunks = array_chunk($productIds, $this->bulkSize);
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Update attributes for ' . count($productIds) . ' selected products');
        $operations = [];
        foreach ($productIdsChunks as $productIdsChunk) {
            if ($websiteRemoveData || $websiteAddData) {
                $dataToUpdate = [
                    'website_assign' => $websiteAddData,
                    'website_detach' => $websiteRemoveData
                ];
                $operations[] = $this->makeOperation(
                    'Update website assign',
                    'product_action_attribute.website.update',
                    $dataToUpdate,
                    $storeId,
                    $websiteId,
                    $productIdsChunk,
                    $bulkUuid
                );
            }

            if ($attributesData) {
                $operations[] = $this->makeOperation(
                    'Update product attributes',
                    'product_action_attribute.update',
                    $attributesData,
                    $storeId,
                    $websiteId,
                    $productIdsChunk,
                    $bulkUuid
                );
            }
            }

        if (!empty($operations)) {
            $result = $this->bulkManagement->scheduleBulk(
                $bulkUuid,
                $operations,
                $bulkDescription,
                $this->userContext->getUserId()
            );
            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }

    private function sanitizeProductAttributes($attributesData)
    {
        $dateFormat = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)->getDateFormat(\IntlDateFormatter::SHORT);
        $config = $this->_objectManager->get(\Magento\Eav\Model\Config::class);

        foreach ($attributesData as $attributeCode => $value) {
            $attribute = $config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if (!$attribute->getAttributeId()) {
                unset($attributesData[$attributeCode]);
                continue;
            }
            if ($attribute->getBackendType() === 'datetime') {
                if (!empty($value)) {
                    $filterInput = new \Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                    $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                        ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
                    );
                    $value = $filterInternal->filter($filterInput->filter($value));
                } else {
                    $value = null;
                }
                $attributesData[$attributeCode] = $value;
            } elseif ($attribute->getFrontendInput() === 'multiselect') {
                // Check if 'Change' checkbox has been checked by admin for this attribute
                $isChanged = (bool)$this->getRequest()->getPost('toggle_' . $attributeCode);
                if (!$isChanged) {
                    unset($attributesData[$attributeCode]);
                    continue;
                }
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $attributesData[$attributeCode] = $value;
            }
        }
        return $attributesData;
    }

    protected function _validateProducts()
    {
        $error = false;
        $productIds = $this->attributeHelper->getProductIds();
        if (!is_array($productIds)) {
            $error = __('Please select products for attributes update.');
        } elseif (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)
        ->isProductsHasSku($productIds)) {
        $error = __('Please make sure to define SKU values for all processed products.');
    }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
        }

        return !$error;
    }

    public function execute()
    {
        if(version_compare($this->productMetaData->getVersion(), "2.3.1", ">")){
            if (!$this->_validateProducts()) {
                return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
            }

            /* Collect Data */
            $attributesData = $this->getRequest()->getParam('attributes', []);
            $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
            $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);

            $storeId = $this->attributeHelper->getSelectedStoreId();
            $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);
            $productIds = $this->attributeHelper->getProductIds();

            $attributesData = $this->sanitizeProductAttributes($attributesData);

            try {
                $this->publish($attributesData, $websiteRemoveData, $websiteAddData, $storeId, $websiteId, $productIds);
                $this->messageManager->addSuccessMessage(__('Message is added to queue'));

                $inventoryRequestData = $this->getRequest()->getParam('inventory', []);

                if ($inventoryRequestData) {
                    $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();

                    $storeId = $this->attributeHelper->getSelectedStoreId();
                    $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);

                    $stockId = $this->stockRegistry->getStock($websiteId)->getStockId();

                    if(
                        ($websiteId == $defaultScopeId)
                        || isset($inventoryRequestData['use_default_values'])
                    ){
                        foreach ($this->attributeHelper->getProductIds() as $productId) {
                            $stockItemDo = $this->stockRegistry->getStockItem(
                                $productId,
                                $websiteId
                            );

                            if(!$stockItemDo->getId()
                                || (
                                    ($websiteId != $defaultScopeId)
                                    && $inventoryRequestData['use_default_values']
                                )
                            ){
                                $defaultStockItem = $this->stockRegistry->getStockItem($productId, $defaultScopeId);

                                $stockItemDoId = $stockItemDo->getId();
                                $stockItemDo->setData($defaultStockItem->getData());

                                $stockItemDo
                                    ->setId($stockItemDoId)
                                    ->setProductId($productId)
                                    ->setWebsiteId($websiteId)
                                    ->setStockId($stockId)
                                    ->setUseDefaultValues($inventoryRequestData['use_default_values']);
                            }


                            $stockItemDoId = $stockItemDo->getId();
                            if(
                            !(
                                ($websiteId != $defaultScopeId)
                                && $inventoryRequestData['use_default_values']
                            )
                            ){
                                $this->dataObjectHelper->populateWithArray(
                                    $stockItemDo,
                                    $inventoryRequestData,
                                    '\Magento\CatalogInventory\Api\Data\StockItemInterface'
                                );
                                $stockItemDo->setUseDefaultValues(0);
                            }

                            $stockItemDo->setItemId($stockItemDoId);

                            $this->stockItemRepository->save($stockItemDo);
                        }
                    }

                    $productIdsToReindex = $this->attributeHelper->getProductIds();
                    $this->stockIndexerProcessor->reindexList($productIdsToReindex);

                    $this->messageManager->addSuccessMessage(__('The inventory data was successfully updated.'));
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while updating the product(s) attributes.')
                );
            }

            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['store' => $storeId]);
        }else{
            $validateProducts = $this->_validateProducts();

            if (!$validateProducts) {
                return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
            }

            /* Collect Data */
            $inventoryRequestData = $this->getRequest()->getParam('inventory', []);
            $attributesRequestData = $this->getRequest()->getParam('attributes', []);
            $websiteRemoveRequestData = $this->getRequest()->getParam('remove_website_ids', []);
            $websiteAddRequestData = $this->getRequest()->getParam('add_website_ids', []);

            /* Prepare inventory data item options (use config settings) */
            $options = $this->_objectManager->get('Magento\CatalogInventory\Api\StockConfigurationInterface')
                ->getConfigItemOptions();
            foreach ($options as $option) {
                if (isset($inventoryRequestData[$option]) && !isset($inventoryRequestData['use_config_' . $option])) {
                    $inventoryRequestData['use_config_' . $option] = 0;
                }
            }

            try {
                $storeId = $this->attributeHelper->getSelectedStoreId();
                if ($attributesRequestData) {
                    $dateFormat = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
                        ->getDateFormat(\IntlDateFormatter::SHORT);

                    foreach ($attributesRequestData as $attributeCode => $value) {
                        $attribute = $this->_objectManager->get('Magento\Eav\Model\Config')
                            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
                        if (!$attribute->getAttributeId()) {
                            unset($attributesRequestData[$attributeCode]);
                            continue;
                        }
                        if ($attribute->getBackendType() == 'datetime') {
                            if (!empty($value)) {
                                $filterInput = new \Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                                $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                                    ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
                                );
                                $value = $filterInternal->filter($filterInput->filter($value));
                            } else {
                                $value = null;
                            }
                            $attributesRequestData[$attributeCode] = $value;
                        } elseif ($attribute->getFrontendInput() == 'multiselect') {
                            // Check if 'Change' checkbox has been checked by admin for this attribute
                            $isChanged = (bool)$this->getRequest()->getPost($attributeCode . '_checkbox');
                            if (!$isChanged) {
                                unset($attributesRequestData[$attributeCode]);
                                continue;
                            }
                            if (is_array($value)) {
                                $value = implode(',', $value);
                            }
                            $attributesRequestData[$attributeCode] = $value;
                        }
                    }

                    $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                        ->updateAttributes($this->attributeHelper->getProductIds(), $attributesRequestData, $storeId);
                }

                if ($inventoryRequestData) {
                    $defaultScopeId = $this->stockConfiguration->getDefaultScopeId();

                    // TODO why use ObjectManager?
                    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
                    $stockRegistry = $this->_objectManager
                        ->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                    /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
                    $stockItemRepository = $this->_objectManager
                        ->create('Magento\CatalogInventory\Api\StockItemRepositoryInterface');

                    $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);
                    $stockId = $stockRegistry->getStock($websiteId)->getStockId();

                    if(
                        ($websiteId == $defaultScopeId)
                        || isset($inventoryRequestData['use_default_values'])
                    ){
                        foreach ($this->attributeHelper->getProductIds() as $productId) {
                            $stockItemDo = $stockRegistry->getStockItem(
                                $productId,
                                $websiteId
                            );

                            if(!$stockItemDo->getId()
                                || (
                                    ($websiteId != $defaultScopeId)
                                    && $inventoryRequestData['use_default_values']
                                )
                            ){
                                $defaultStockItem = $stockRegistry->getStockItem($productId, $defaultScopeId);

                                $stockItemDoId = $stockItemDo->getId();
                                $stockItemDo->setData($defaultStockItem->getData());

                                $stockItemDo
                                    ->setId($stockItemDoId)
                                    ->setProductId($productId)
                                    ->setWebsiteId($websiteId)
                                    ->setStockId($stockId)
                                    ->setUseDefaultValues($inventoryRequestData['use_default_values']);
                            }


                            $stockItemDoId = $stockItemDo->getId();
                            if(
                            !(
                                ($websiteId != $defaultScopeId)
                                && $inventoryRequestData['use_default_values']
                            )
                            ){
                                $this->dataObjectHelper->populateWithArray(
                                    $stockItemDo,
                                    $inventoryRequestData,
                                    '\Magento\CatalogInventory\Api\Data\StockItemInterface'
                                );
                                $stockItemDo->setUseDefaultValues(0);
                            }

                            $stockItemDo->setItemId($stockItemDoId);

                            $stockItemRepository->save($stockItemDo);
                        }
                    }

                    $productIdsToReindex = $this->attributeHelper->getProductIds();
                    $this->stockIndexerProcessor->reindexList($productIdsToReindex);
                }

                if ($websiteAddRequestData || $websiteRemoveRequestData) {
                    /* @var $actionModel \Magento\Catalog\Model\Product\Action */
                    $actionModel = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');
                    $productIds = $this->attributeHelper->getProductIds();

                    if ($websiteRemoveRequestData) {
                        $actionModel->updateWebsites($productIds, $websiteRemoveRequestData, 'remove');
                    }
                    if ($websiteAddRequestData) {
                        $actionModel->updateWebsites($productIds, $websiteAddRequestData, 'add');
                    }

                    $this->_eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
                }

                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) were updated.', count($this->attributeHelper->getProductIds()))
                );

                $this->productFlatIndexerProcessor->reindexList($this->attributeHelper->getProductIds());

                if ($this->catalogProduct->isDataForPriceIndexerWasChanged($attributesRequestData)
                    || !empty($websiteRemoveRequestData)
                    || !empty($websiteAddRequestData)
                ) {
                    $this->productPriceIndexerProcessor->reindexList($this->attributeHelper->getProductIds());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating the product(s) attributes.')
                );
            }

            return $this->resultRedirectFactory->create()
                ->setPath('catalog/product/', ['store' => $this->attributeHelper->getSelectedStoreId()]);
        }
    }
}