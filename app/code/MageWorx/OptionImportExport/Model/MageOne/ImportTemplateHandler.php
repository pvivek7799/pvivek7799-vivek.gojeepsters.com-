<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionImportExport\Model\MageOne;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\File\Csv as FileCsv;
use Magento\Backend\Model\Session as BackendSession;
use MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;
use MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use MageWorx\OptionBase\Model\Product\Option\Value\Attributes as ValueAttributes;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionTemplates\Model\GroupFactory;
use MageWorx\OptionTemplates\Model\Group\OptionFactory as GroupOptionFactory;
use MageWorx\OptionImportExport\Helper\Data as Helper;

class ImportTemplateHandler
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * CSV Processor
     *
     * @var FileCsv
     */
    protected $csvProcessor;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var ValueAttributes
     */
    protected $valueAttributes;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupOptionFactory
     */
    protected $groupOptionFactory;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * List of fields that must be validated in group, except APO attributes
     *
     * @var string[]
     */
    protected $groupValidationRules = [
        'not-empty' => [
            'title',
            'group_id'
        ],
        'required'  => [
            'is_active',
            'updated_at'
        ]
    ];

    /**
     * List of fields that must be in group option, except APO attributes
     *
     * @var string[]
     */
    protected $optionValidationRules = [
        'not-empty' => [
            'title',
            'option_id',
            'type'
        ],
        'required'  => [
            'is_require',
            'sort_order',
        ],
        'optional'  => [
            'price',
            'price_type',
            'sku',
            'max_characters',
            'file_extension',
            'image_size_x',
            'image_size_y'
        ]
    ];

    /**
     * List of fields that must be in group option value, except APO attributes
     *
     * @var string[]
     */
    protected $valueValidationRules = [
        'not-empty' => [
            'title',
            'option_type_id',
            'price_type',
        ],
        'required'  => [
            'price',
            'sort_order',
            'sku'
        ]
    ];

    /**
     * Array of dependencies from M1
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * Option to value map from M1
     *
     * @var array
     */
    protected $optionValueMap = [];

    /**
     * @var int
     */
    protected $currentMageOneGroupId;

    /**
     * @var array
     */
    protected $customerGroupMap = [];

    /**
     * @var array
     */
    protected $storeIdMap = [];

    /**
     * @var array
     */
    protected $customerEquivalentMap = [];

    /**
     * @var array
     */
    protected $storeEquivalentMap = [];

    /**
     * @var array
     */
    protected $missingImagesList = [];

    /**
     * @var array
     */
    protected $storeOptionDescriptions = [];

    /**
     * @var array
     */
    protected $storeValueDescriptions = [];


    /**
     * ImportTemplateHandler constructor.
     *
     * @param Escaper $escaper
     * @param FileCsv $csvProcessor
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param ValueAttributes $valueAttributes
     * @param BaseHelper $baseHelper
     * @param Helper $helper
     * @param EventManager $eventManager
     * @param Filesystem $filesystem
     * @param HttpRequest $request
     * @param GroupFactory $groupFactory
     * @param GroupOptionFactory $groupOptionFactory
     * @param BackendSession $backendSession
     */
    public function __construct(
        Escaper $escaper,
        FileCsv $csvProcessor,
        ProductAttributes $productAttributes,
        OptionAttributes $optionAttributes,
        ValueAttributes $valueAttributes,
        BaseHelper $baseHelper,
        Helper $helper,
        EventManager $eventManager,
        Filesystem $filesystem,
        HttpRequest $request,
        BackendSession $backendSession,
        GroupFactory $groupFactory,
        GroupOptionFactory $groupOptionFactory
    ) {
        $this->csvProcessor       = $csvProcessor;
        $this->escaper            = $escaper;
        $this->productAttributes  = $productAttributes;
        $this->optionAttributes   = $optionAttributes;
        $this->valueAttributes    = $valueAttributes;
        $this->baseHelper         = $baseHelper;
        $this->helper             = $helper;
        $this->groupFactory       = $groupFactory;
        $this->groupOptionFactory = $groupOptionFactory;
        $this->eventManager       = $eventManager;
        $this->request            = $request;
        $this->backendSession     = $backendSession;
        $this->mediaDirectory     = $filesystem->getDirectoryWrite('media');
    }

    /**
     * Import group data from file
     *
     * @param array $file file info retrieved from $_FILES array
     * @param array $map
     * @throws \Exception
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws LocalizedException
     */
    public function importFromFile($file, $map)
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt'));
        }
        $fileData = $this->csvProcessor->getData($file['tmp_name']);
        if (!is_array($fileData) || !$fileData) {
            throw new LocalizedException(
                __('Invalid file data')
            );
        }

        $serializedData = implode(',', $fileData[0]);
        $data           = unserialize($serializedData);
        if (!is_array($data) || !$data) {
            throw new LocalizedException(
                __('Data for import not found')
            );
        }

        $this->validateData($data);
        if (!empty($map['mageworx_optiontemplates_import_mage_one_customer_groups'])) {
            $this->customerEquivalentMap          = $map['mageworx_optiontemplates_import_mage_one_customer_groups'];
            $this->customerEquivalentMap['32000'] = '32000';
        }
        if (!empty($map['mageworx_optiontemplates_import_mage_one_stores'])) {
            $this->storeEquivalentMap = $map['mageworx_optiontemplates_import_mage_one_stores'];
        }
        $this->validateSystemData($data);
        $this->importData($data);
    }

    /**
     * Validate data integrity
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateData($data)
    {
        foreach ($data as $dataItem) {
            if (empty($dataItem) || !is_array($dataItem)) {
                throw new LocalizedException(
                    __('Data for option template not found')
                );
            }
            $this->validateGroup($dataItem);
        }
    }

    /**
     * Validate group integrity
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateGroup($data)
    {
        $this->dependencies   = [];
        $this->optionValueMap = [];
        $this->validateGroupDefaults($data);
        $this->validateGroupAttributes($data);
        $this->validateOptions($data);
    }

    /**
     * Validate group default fields
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateGroupDefaults($data)
    {
        foreach ($this->groupValidationRules['not-empty'] as $field) {
            if (!empty($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Template's field '%1' not found or empty", $field)
            );
        }
        foreach ($this->groupValidationRules['required'] as $field) {
            if (isset($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Template's field '%1' not found", $field)
            );
        }
    }

    /**
     * Validate group APO attributes
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateGroupAttributes($data)
    {
        $productAttributes = $this->productAttributes->getData();
        foreach ($productAttributes as $productAttribute) {
            /** @var \MageWorx\OptionBase\Api\ProductAttributeInterface $productAttribute */
            $productAttribute->validateTemplateImportMageOne($data);
        }
    }

    /**
     * Validate group options integrity
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateOptions($data)
    {
        if (!isset($data['hash_options'])) {
            return;
        }
        $options = unserialize($data['hash_options']);
        if (!is_array($options)) {
            return;
        }

        foreach ($options as $optionData) {
            if (isset($optionData['in_group_id'])) {
                $this->optionValueMap[$optionData['in_group_id']][$optionData['option_id']] = '';
            }
            $this->validateOptionDefaults($optionData);
            $this->validateOptionAttributes($optionData);
            $this->validateValues($optionData);
        }

        foreach ($options as $optionData) {
            $this->addDependenciesChildPart($optionData);
        }
    }

    /**
     * Validate group options default fields
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateOptionDefaults($data)
    {
        foreach ($this->optionValidationRules['not-empty'] as $field) {
            if (!empty($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Option's field '%1' not found or empty", $field)
            );
        }
        foreach ($this->optionValidationRules['required'] as $field) {
            if (isset($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Option's field '%1' not found", $field)
            );
        }
    }

    /**
     * Validate group's APO option attributes
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateOptionAttributes($data)
    {
        $optionAttributes = $this->optionAttributes->getData();
        foreach ($optionAttributes as $optionAttribute) {
            /** @var \MageWorx\OptionBase\Api\AttributeInterface $optionAttribute */
            $optionAttribute->validateTemplateImportMageOne($data);
        }
    }

    /**
     * Validate group's option values integrity
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateValues($data)
    {
        if ((!isset($data['values']) || !is_array($data['values']))) {
            if (in_array($data['type'], $this->baseHelper->getSelectableOptionTypes())) {
                throw new LocalizedException(
                    __("Selectable option doesn't have values")
                );
            }
            return;
        }

        foreach ($data['values'] as $valueData) {
            $this->validateValueDefaults($valueData);
            $this->validateValueAttributes($valueData);

            $this->collectMageOneDependencies($data, $valueData);
        }
    }

    /**
     * Validate group option value's default fields
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateValueDefaults($data)
    {
        foreach ($this->valueValidationRules['not-empty'] as $field) {
            if (!empty($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Option value's field '%1' not found or empty", $field)
            );
        }
        foreach ($this->valueValidationRules['required'] as $field) {
            if (isset($data[$field])) {
                continue;
            }
            throw new LocalizedException(
                __("Option value's field '%1' not found", $field)
            );
        }
    }

    /**
     * Validate group's APO option value attributes
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateValueAttributes($data)
    {
        $valueAttributes = $this->valueAttributes->getData();
        foreach ($valueAttributes as $valueAttribute) {
            /** @var \MageWorx\OptionBase\Api\AttributeInterface $valueAttribute */
            $valueAttribute->validateTemplateImportMageOne($data);
        }
    }

    /**
     * Collect M1 dependencies
     *
     * @param array $m1option
     * @param array $m1value
     * @return void
     */
    protected function collectMageOneDependencies($m1option, $m1value)
    {
        if (!isset($m1value['in_group_id'])) {
            return;
        }
        $this->optionValueMap[$m1value['in_group_id']][$m1option['option_id']] = $m1value['option_type_id'];

        if (empty($m1value['dependent_ids'])) {
            return;
        }

        $childDependencyIds = explode(',', $m1value['dependent_ids']);
        foreach ($childDependencyIds as $childDependencyId) {
            $this->dependencies[] = [
                'parent_option_id'      => $m1option['option_id'],
                'parent_option_type_id' => $m1value['option_type_id'],
                'child_option_id'       => '',
                'child_option_type_id'  => '',
                'in_group_id'           => $childDependencyId,
                'dependency_type'       => isset($m1option['is_dependent']) ? (int)$m1option['is_dependent'] : 0,
            ];
        }
    }

    /**
     * Validate customer groups and store IDs data, because they may be different for M1 and M2
     *
     * @param array $data
     * @throws IntegrationException
     */
    protected function validateSystemData($data)
    {
        foreach ($data as $dataItem) {
            $this->collectSystemDataFromOptions($dataItem);
            $this->collectSystemDataFromStores($dataItem);
        }

        if ($this->isSystemDataEquivalentMapNeeded()) {
            $this->backendSession->setStoreIds($this->storeIdMap);
            $this->backendSession->setCustomerGroupIds($this->customerGroupMap);
            throw new IntegrationException(
                __("Please, link system specific data for Magento/Magento 2 and upload the file once again.")
            );
        } elseif ($this->hasSystemDataEquivalentMapDuplicates()) {
            $this->backendSession->setStoreIds($this->storeIdMap);
            $this->backendSession->setCustomerGroupIds($this->customerGroupMap);
            throw new IntegrationException(
                __("Please, avoid assignment of stores and customer groups to the same entities.")
            );
        }
    }

    /**
     * Check if it is needed to set system data equivalent map (M1 to M2)
     *
     * @return bool
     */
    protected function isSystemDataEquivalentMapNeeded()
    {
        return $this->hasMageOneSystemData() && !$this->isSystemDataEquivalentMapExist();
    }

    /**
     * Check if equivalent map has duplicates of selected values
     *
     * @return bool
     */
    protected function hasSystemDataEquivalentMapDuplicates()
    {
        if ($this->hasMageOneSystemData() && !$this->isSystemDataEquivalentMapExist()) {
            return false;
        }

        if (!empty($this->customerEquivalentMap)
            && count($this->customerEquivalentMap) !== count(array_unique($this->customerEquivalentMap))
        ) {
            return true;
        }

        if (!empty($this->storeEquivalentMap)
            && count($this->storeEquivalentMap) !== count(array_unique($this->storeEquivalentMap))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if M1 file has any data about customer groups and stores
     *
     * @return bool
     */
    protected function hasMageOneSystemData()
    {
        return !empty($this->customerGroupMap) || !empty($this->storeIdMap);
    }

    /**
     * Check if customer groups and stores equivalent map exists
     *
     * @return bool
     */
    protected function isSystemDataEquivalentMapExist()
    {
        return !empty($this->customerEquivalentMap) || !empty($this->storeEquivalentMap);
    }

    /**
     * Collect system data from options (customer group IDs, store IDs) to map
     *
     * @param array $data
     */
    protected function collectSystemDataFromOptions($data)
    {
        if (!isset($data['hash_options'])) {
            return;
        }
        $options = unserialize($data['hash_options']);
        if (!is_array($options)) {
            return;
        }

        foreach ($options as $optionData) {
            if (isset($optionData['store_views']) && is_array($optionData['store_views'])) {
                foreach ($optionData['store_views'] as $storeId) {
                    $this->storeIdMap[$storeId] = $storeId;
                }
            }

            if (isset($optionData['customer_groups']) && is_array($optionData['customer_groups'])) {
                foreach ($optionData['customer_groups'] as $customerGroup) {
                    if ($customerGroup == '32000') {
                        continue;
                    }
                    $this->customerGroupMap[$customerGroup] = $customerGroup;
                }
            }

            if ((!isset($optionData['values']) || !is_array($optionData['values']))) {
                continue;
            }

            foreach ($optionData['values'] as $valueData) {
                if (isset($valueData['specials']) && is_array($valueData['specials'])) {
                    foreach ($valueData['specials'] as $item) {
                        if ($item['customer_group_id'] == '32000') {
                            continue;
                        }
                        $this->customerGroupMap[$item['customer_group_id']] = $item['customer_group_id'];
                    }
                }

                if (isset($valueData['tiers']) && is_array($valueData['tiers'])) {
                    foreach ($valueData['tiers'] as $item) {
                        if ($item['customer_group_id'] == '32000') {
                            continue;
                        }
                        $this->customerGroupMap[$item['customer_group_id']] = $item['customer_group_id'];
                    }
                }
            }
        }
    }

    /**
     * Collect system data from store views (customer group IDs, store IDs) to map
     *
     * @param array $data
     */
    protected function collectSystemDataFromStores($data)
    {
        if (!isset($data['stores'])) {
            return;
        }
        foreach ($data['stores'] as $store) {
            if (!isset($store['hash_options']) || !isset($store['store_id'])) {
                continue;
            }
            $this->storeIdMap[$store['store_id']] = $store['store_id'];
        }
    }

    /**
     * Import data
     *
     * @param array $data
     */
    protected function importData($data)
    {
        foreach ($data as $dataItem) {
            $this->importGroup($dataItem);
        }
    }

    /**
     * Import data for group
     *
     * @param array $data
     */
    protected function importGroup($data)
    {
        /** @var $group \MageWorx\OptionTemplates\Model\Group */
        $group                       = $this->groupFactory->create();
        $this->currentMageOneGroupId = $data['group_id'];
        $this->prepareSkuPolicy($data);

        $this->storeOptionDescriptions = [];
        $this->storeValueDescriptions  = [];
        $this->collectStoreViewDescriptions($data);

        if (!isset($data['hash_options'])) {
            $data['options'] = [];
            $group->setData($data);
            $group->unsetData($group->getIdFieldName());
            $group->setId(null);
        } else {
            $options = unserialize($data['hash_options']);
            if (is_array($options)) {
                $preparedOptions = $this->prepareOptions($options);
                if ($preparedOptions) {
                    $data['options']         = $preparedOptions;
                    $data['product_options'] = $preparedOptions;
                    $group->setOptions($preparedOptions);
                }
                $data['hash_options'] = [];
            }
            $group->setData($data);
            $group->unsetData($group->getIdFieldName());
            $group->setId(null);
            $group->setCanSaveCustomOptions(true);
        }

        $group->setIsUniqueTitleNeeded(true);
        $group->save();
    }

    /**
     * Collect store view descriptions
     *
     * @param array $data
     * @return void
     */
    protected function collectStoreViewDescriptions($data)
    {
        foreach ($data['stores'] as $store) {
            if (!isset($store['hash_options']) || !isset($store['store_id'])) {
                continue;
            }
            $storeOptions = unserialize($store['hash_options']);
            if (!is_array($storeOptions)) {
                continue;
            }
            foreach ($storeOptions as $storeOption) {
                if (isset($storeOption['option_id'])
                    && isset($storeOption['description'])
                    && !empty($this->storeEquivalentMap[$store['store_id']])
                ) {
                    $this->storeOptionDescriptions[$storeOption['option_id']][] = [
                        'store_id'    => $store['store_id'],
                        'description' => $storeOption['description']
                    ];
                }
                if (!isset($storeOption['values']) || !is_array($storeOption['values'])) {
                    continue;
                }
                foreach ($storeOption['values'] as $storeValue) {
                    if (!isset($storeValue['option_type_id'])
                        || !isset($storeValue['description'])
                        || empty($this->storeEquivalentMap[$store['store_id']])
                    ) {
                        continue;
                    }
                    $this->storeValueDescriptions[$storeValue['option_type_id']][] = [
                        'store_id'    => $this->storeEquivalentMap[$store['store_id']],
                        'description' => $storeValue['description']
                    ];
                }
            }
        }
    }

    /**
     * Prepare data for product SKU Policy
     *
     * @param array $data
     * @return void
     */
    protected function prepareSkuPolicy(&$data)
    {
        $map = [
            '0' => 'use_config',
            '1' => 'standard',
            '2' => 'independent',
            '3' => 'grouped',
            '4' => 'replacement',
        ];
        if (!isset($data['sku_policy']) || !isset($map[$data['sku_policy']])) {
            $data['sku_policy'] = 'use_config';
            return;
        }
        $data['sku_policy'] = $map[$data['sku_policy']];
    }

    /**
     * Prepare option's data for group import
     * Remove option_ids/option_type_ids
     * Add record_id to simulate dynamicRows saving
     * Collect M1 dependency data
     *
     * @param array $m1options
     * @return array
     */
    protected function prepareOptions($m1options)
    {
        $preparedOptions = [];

        if (!is_array($m1options)) {
            return [];
        }
        $sortOrderCounter = 1;
        foreach ($m1options as $m1option) {
            $m1option['is_swatch'] = 0;
            if ($m1option['type'] == 'swatch') {
                $optionType            = 'drop_down';
                $m1option['is_swatch'] = 1;
            } elseif ($m1option['type'] == 'multiswatch') {
                $optionType            = 'checkbox';
                $m1option['is_swatch'] = 1;
            } elseif ($m1option['type'] == 'hidden') {
                $optionType = 'checkbox';
            } else {
                $optionType = $m1option['type'];
            }
            $this->prepareImages($m1option);

            $optionData = [
                'record_id'  => $m1option['option_id'],
                'type'       => $optionType,
                'is_require' => $m1option['is_require'],
                'sort_order' => (string)$sortOrderCounter
            ];

            $standardOptionFields = [
                'title',
                'price',
                'sku',
                'max_characters',
                'file_extension',
                'image_size_x',
                'image_size_y'
            ];
            foreach ($standardOptionFields as $standardOptionField) {
                if (isset($m1option[$standardOptionField])) {
                    $optionData[$standardOptionField] = $m1option[$standardOptionField];
                }
            }
            if (isset($m1option['price_type'])) {
                $optionData['price_type'] = $m1option['price_type'] == 'fixed' ? 'fixed' : 'percent';
            }

            $this->addDependenciesParentPart($m1option);
            $this->prepareDescriptions($m1option);
            $this->prepareOptionSystemData($m1option);

            $optionAttributes = $this->optionAttributes->getData();
            foreach ($optionAttributes as $optionAttribute) {
                /** @var \MageWorx\OptionBase\Api\AttributeInterface $optionAttribute */
                $optionData[$optionAttribute->getName()] = $optionAttribute->importTemplateMageOne($m1option);
            }
            if (isset($m1option['values']) && is_array($m1option['values'])) {
                $optionData['values'] = $this->prepareOptionValues($m1option);
            }

            $preparedOptions[] = $this->groupOptionFactory->create()->setData($optionData);
            $sortOrderCounter++;
        }
        return $preparedOptions;
    }

    /**
     * Prepare data for different store views
     *
     * @param array $m1option
     * @return void
     */
    protected function prepareDescriptions(&$m1option)
    {
        if (!isset($m1option['option_id']) || !isset($m1option['description'])) {
            return;
        }

        $currentOptionDescription  = $m1option['description'];
        $m1option['description']   = [];
        $m1option['description'][] = [
            'description' => $currentOptionDescription,
            'store_id'    => 0
        ];
        if (!empty($this->storeOptionDescriptions[$m1option['option_id']])) {
            foreach ($this->storeOptionDescriptions[$m1option['option_id']] as $storeOptionDescription) {
                $m1option['description'][] = [
                    'description' => $storeOptionDescription['description'],
                    'store_id'    => $storeOptionDescription['store_id']
                ];
            }
        }
        if (empty($m1option['values'])) {
            return;
        }
        foreach ($m1option['values'] as &$value) {
            if (empty($this->storeValueDescriptions[$value['option_type_id']])) {
                continue;
            }

            $currentValueDescription = $value['description'];
            $value['description']    = [];
            $value['description'][]  = [
                'description' => $currentValueDescription,
                'store_id'    => 0
            ];
            foreach ($this->storeValueDescriptions[$value['option_type_id']] as $storeValueDescription) {
                $value['description'][] = [
                    'description' => $storeValueDescription['description'],
                    'store_id'    => $storeValueDescription['store_id']
                ];
            }
        }
    }

    /**
     * Change customer groups/stores data to M2 equivalent
     *
     * @param array $optionData
     * @return void
     */
    protected function prepareOptionSystemData(&$optionData)
    {
        $this->prepareOptionStoresData($optionData);
        $this->prepareOptionCustomerGroupsData($optionData);
    }

    /**
     * Change stores data to M2 equivalent
     *
     * @param array $optionData
     * @return void
     */
    protected function prepareOptionStoresData(&$optionData)
    {
        if (isset($optionData['store_views']) && is_array($optionData['store_views'])) {
            $stores    = [];
            $isEnabled = 0;
            foreach ($optionData['store_views'] as &$storeView) {
                if (isset($this->storeEquivalentMap[$storeView])
                    && $this->storeEquivalentMap[$storeView] !== ''
                ) {
                    $stores[]                  = $this->storeEquivalentMap[$storeView];
                    $optionData['is_disabled'] = 0;
                    $isEnabled                 = 1;
                } elseif (!$isEnabled) {
                    $optionData['is_disabled'] = 1;
                }
            }
            $optionData['store_views'] = $stores;
        }
    }

    /**
     * Change customer groups data to M2 equivalent
     *
     * @param array $optionData
     * @return void
     */
    protected function prepareOptionCustomerGroupsData(&$optionData)
    {
        if (isset($optionData['customer_groups']) && is_array($optionData['customer_groups'])) {
            $customerGroups = [];
            $isEnabled      = 0;
            foreach ($optionData['customer_groups'] as &$customerGroup) {
                if (isset($this->customerEquivalentMap[$customerGroup])
                    && $this->customerEquivalentMap[$customerGroup] !== ''
                ) {
                    $customerGroups[]          = $this->customerEquivalentMap[$customerGroup];
                    $optionData['is_disabled'] = 0;
                    $isEnabled                 = 1;
                } elseif (!$isEnabled) {
                    $optionData['is_disabled'] = 1;
                }
            }
            $optionData['customer_groups'] = $customerGroups;
        }
    }

    /**
     * Add dependencies parent_option_id, parent_option_type_id and dependency_type
     *
     * @param array $m1option
     * @return void
     */
    protected function addDependenciesParentPart(&$m1option)
    {
        if (empty($this->dependencies) || !is_array($this->dependencies)) {
            return;
        }

        $childOptionId = $m1option['option_id'];
        if (!in_array($m1option['type'], $this->baseHelper->getSelectableOptionTypes())) {
            foreach ($this->dependencies as $dependency) {
                if ($dependency['child_option_id'] == $childOptionId && $dependency['child_option_type_id'] == '') {
                    $m1option['dependency'][]    = [
                        (int)$dependency['parent_option_id'],
                        (int)$dependency['parent_option_type_id']
                    ];
                    $m1option['dependency_type'] = (int)$dependency['dependency_type'];
                }
            }
        }

        if (!isset($m1option['values']) || !is_array($m1option['values'])) {
            return;
        }

        foreach ($m1option['values'] as &$m1value) {
            $childOptionTypeId = $m1value['option_type_id'];

            foreach ($this->dependencies as $dependency) {
                if ($dependency['child_option_id'] == $childOptionId
                    && $dependency['child_option_type_id'] == $childOptionTypeId
                ) {
                    $m1value['dependency'][]    = [
                        (int)$dependency['parent_option_id'],
                        (int)$dependency['parent_option_type_id']
                    ];
                    $m1value['dependency_type'] = (int)$dependency['dependency_type'];
                }
            }
        }
    }

    /**
     * Prepare value's data for group import
     *
     * @param array $m1option
     * @return array
     */
    protected function prepareOptionValues($m1option)
    {
        $preparedOptionValues = [];

        if (!isset($m1option['values']) || !is_array($m1option['values'])) {
            return [];
        }
        $sortOrderCounter = 1;
        foreach ($m1option['values'] as $m1value) {
            $m1value['is_dependent'] = isset($m1option['is_dependent']) ? (int)$m1option['is_dependent'] : 0;
            $valueData               = [
                'record_id'  => $m1value['option_type_id'],
                'price_type' => $m1value['price_type'] == 'fixed' ? 'fixed' : 'percent',
                'price'      => $m1value['price'],
                'title'      => $m1value['title'],
                'sku'        => $m1value['sku'],
                'sort_order' => (string)$sortOrderCounter
            ];

            if (!empty($m1option['default'])) {
                $default = array_flip($m1option['default']);
                if (array_key_exists($m1value['option_type_id'], $default)) {
                    $m1value['is_default'] = '1';
                }
            }

            $this->prepareValueSystemData($m1value);

            $valueAttributes = $this->valueAttributes->getData();
            foreach ($valueAttributes as $valueAttribute) {
                /** @var \MageWorx\OptionBase\Api\AttributeInterface $valueAttribute */
                $valueData[$valueAttribute->getName()] = $valueAttribute->importTemplateMageOne($m1value);
            }

            $preparedOptionValues[] = $valueData;
            $sortOrderCounter++;
        }

        return $preparedOptionValues;
    }

    /**
     * Add dependencies child_option_id, child_option_type_id according to M1 in_group_id
     *
     * @param array $option
     * @return void
     */
    protected function addDependenciesChildPart($option)
    {
        foreach ($this->optionValueMap[$option['in_group_id']] as $optionId => $optionTypeId) {
            foreach ($this->dependencies as &$dependency) {
                if ($dependency['in_group_id'] == $option['in_group_id']) {
                    $dependency['child_option_id']      = $optionId;
                    $dependency['child_option_type_id'] = $optionTypeId;
                }
            }
        }

        if (empty($option['values'])) {
            return;
        }

        foreach ($option['values'] as $value) {
            foreach ($this->optionValueMap[$value['in_group_id']] as $optionId => $optionTypeId) {
                foreach ($this->dependencies as &$dependency) {
                    if ($dependency['in_group_id'] == $value['in_group_id']) {
                        $dependency['child_option_id']      = $optionId;
                        $dependency['child_option_type_id'] = $optionTypeId;
                    }
                }
            }
        }
    }

    /**
     * Change special/tier price customer groups to M2 equivalent
     *
     * @param array $valueData
     * @return void
     */
    protected function prepareValueSystemData(&$valueData)
    {
        if (isset($valueData['specials']) && is_array($valueData['specials'])) {
            $specials = [];
            foreach ($valueData['specials'] as $item) {
                if (isset($this->customerEquivalentMap[$item['customer_group_id']])
                    && $this->customerEquivalentMap[$item['customer_group_id']] !== ''
                ) {
                    $item['customer_group_id'] = $this->customerEquivalentMap[$item['customer_group_id']];
                    if ($item['price_type'] == 'percent' || $item['price_type'] == 'optperc') {
                        $item['price_type'] = 'percent';
                    } elseif ($item['price_type'] == 'fixed') {
                        $item['price_type'] = 'fixed';
                    } else {
                        continue;
                    }
                    $specials[] = $item;
                }
            }
            $valueData['specials'] = $specials;
        }

        if (isset($valueData['tiers']) && is_array($valueData['tiers'])) {
            $tiers = [];
            foreach ($valueData['tiers'] as $item) {
                if (isset($this->customerEquivalentMap[$item['customer_group_id']])
                    && $this->customerEquivalentMap[$item['customer_group_id']] !== ''
                ) {
                    $item['customer_group_id'] = $this->customerEquivalentMap[$item['customer_group_id']];
                    if ($item['price_type'] == 'percent' || $item['price_type'] == 'optperc') {
                        $item['price_type'] = 'percent';
                    } elseif ($item['price_type'] == 'fixed') {
                        $item['price_type'] = 'fixed';
                    } else {
                        continue;
                    }
                    $tiers[] = $item;
                }
            }
            $valueData['tiers'] = $tiers;
        }
    }

    /**
     * Prepare images according to M2 requirements
     * Copy image files to M2 media APO directory
     *
     * @param array $option
     * @throws FileSystemException
     * @return void
     */
    protected function prepareImages(&$option)
    {
        $groupId  = $this->currentMageOneGroupId;
        $optionId = $option['option_id'];
        if (empty($option['values']) || !is_array($option['values'])) {
            return;
        }
        foreach ($option['values'] as &$value) {
            $valueId = $value['option_type_id'];
            if (empty($value['images']) || !is_array($value['images'])) {
                continue;
            }
            foreach ($value['images'] as $fileName) {
                if ($this->isColorCode($fileName)) {
                    continue;
                }
                $defaultMageOneDirectoryPath = 'mageworx/customoptions/';

                $sourcePath = $defaultMageOneDirectoryPath
                    . $groupId
                    . '/'
                    . $optionId
                    . '/'
                    . $valueId
                    . '/'
                    . $fileName;

                $filePath =
                    '/'
                    . strtolower(substr($fileName, 0, 1))
                    . '/'
                    . strtolower(substr($fileName, 1, 1))
                    . '/'
                    . $fileName;

                $value['images_data'][] = $filePath;
                $destinationPath        = 'mageworx/optionfeatures/product/option/value' . $filePath;
                try {
                    $this->mediaDirectory->copyFile($sourcePath, $destinationPath);
                } catch (FileSystemException $e) {
                    if ($this->helper->isIgnoreMissingImages()) {
                        $this->missingImagesList[] = $sourcePath;
                        continue;
                    } else {
                        $this->backendSession->setStoreIds($this->storeIdMap);
                        $this->backendSession->setCustomerGroupIds($this->customerGroupMap);
                        throw new FileSystemException(__("The '%1' file doesn't exist.", $sourcePath));
                    }
                }
            }
        }
    }

    /**
     * Check if filename contains color code
     *
     * @param $fileName
     * @return bool
     */
    protected function isColorCode($fileName)
    {
        return substr($fileName, 0, 1) === '#';
    }

    /**
     * Return missing images list (exist in database, missing in filesystem)
     *
     * @return array
     */
    public function getMissingImagesList()
    {
        return $this->missingImagesList ?: [];
    }
}