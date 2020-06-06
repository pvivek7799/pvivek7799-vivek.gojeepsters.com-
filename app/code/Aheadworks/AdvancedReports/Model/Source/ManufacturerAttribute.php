<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class ManufacturerAttribute
 * @package Aheadworks\AdvancedReports\Model\Source
 */
class ManufacturerAttribute implements OptionSourceInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var array
     */
    private $options;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $this->sortOrderBuilder
                ->setField(ProductAttributeInterface::FRONTEND_LABEL)
                ->setAscendingDirection();
            $this->searchCriteriaBuilder
                ->addFilter(ProductAttributeInterface::FRONTEND_INPUT, 'select')
                ->addSortOrder($this->sortOrderBuilder->create());
            $attributes = $this->productAttributeRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();

            foreach ($attributes as $attribute) {
                if ($attribute->getIsGlobal() && $attribute->getIsUserDefined()) {
                    $this->options[] = [
                        'value' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontendLabel()
                    ];
                }
            }
        }

        return $this->options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = $this->toOptionArray();
        $result = [];

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
