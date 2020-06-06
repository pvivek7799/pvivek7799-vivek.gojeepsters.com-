<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Product\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Api\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractAttribute implements AttributeInterface
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
     * Get attribute name
     *
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * Check if attribute has own table in database
     *
     * @return bool
     */
    public function hasOwnTable()
    {
        return false;
    }

    /**
     * Get table name
     * Used when attribute has individual table
     *
     * @param string $type
     */
    public function getTableName($type = '')
    {
        return;
    }

    /**
     * Collect attribute data
     *
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     * @param array $options
     * @return void
     */
    public function collectData($entity, array $options)
    {
        $this->entity = $entity;

        return;
    }

    /**
     * Delete old attribute data
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        return;
    }

    /**
     * Prepare attribute data before save
     * Returns modified value, which is ready for db save
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value|array $data
     * @return string
     */
    public function prepareDataBeforeSave($data)
    {
        if (is_object($data)) {
            return $data->getData($this->getName());
        } elseif (is_array($data) && isset($data[$this->getName()])) {
            return $data[$this->getName()];
        }
        return '';
    }

    /**
     * Prepare attribute data for frontend js config
     *
     * @param \Magento\Catalog\Model\Product\Option|\Magento\Catalog\Model\Product\Option\Value $object
     * @return array
     */
    public function prepareDataForFrontend($object)
    {
        return [$this->getName() => $object->getData($this->getName())];
    }

    /**
     * Process attribute in case of product/group duplication
     *
     * @param string $newId
     * @param string $oldId
     * @param string $entityType
     */
    public function processDuplicate($newId, $oldId, $entityType = 'product')
    {
        return;
    }

    /**
     * Validate Magento 1 template import
     *
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
     *
     * @param array $data
     * @return int|string|
     */
    public function importTemplateMageOne($data)
    {
        return isset($data[$this->getName()]) ? $data[$this->getName()] : 0;
    }
}
