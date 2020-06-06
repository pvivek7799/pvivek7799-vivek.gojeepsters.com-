<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Product;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Api\ProductAttributeInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractProductAttribute implements ProductAttributeInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function clearData()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyData($entity)
    {
        $this->entity = $entity;

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemByProduct($product)
    {
        return;
    }

    /**
     * Validate Magento 1 template import
     * @param array $groupData
     * @throws \Exception
     * @throws LocalizedException
     */
    public function validateTemplateImportMageOne($groupData)
    {
        return;
    }

    /**
     * Import Magento 1 template data
     * @param array $groupData
     * @return array
     */
    public function importTemplateMageOne($groupData)
    {
        return [];
    }
}
