<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Api;

interface ProductAttributeInterface
{
    /**
     * Get array of attribute keys
     * @return array
     */
    public function getKeys();

    /**
     * Get table name, used when attribute use individual tables
     * @return string
     */
    public function getTableName();

    /**
     * Apply attribute data
     * @param \MageWorx\OptionBase\Model\Entity\Group|\MageWorx\OptionBase\Model\Entity\Product $entity
     * @return void
     */
    public function applyData($entity);

    /**
     * Clear attribute data
     * @return void
     */
    public function clearData();

    /**
     * Get object item by product ID
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\DataObject|null
     */
    public function getItemByProduct($product);

    /**
     * Validate Magento 1 template import
     * @param array $groupData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateTemplateImportMageOne($groupData);

    /**
     * Import Magento 1 template data
     * @param array $groupData
     * @return array
     */
    public function importTemplateMageOne($groupData);
}
