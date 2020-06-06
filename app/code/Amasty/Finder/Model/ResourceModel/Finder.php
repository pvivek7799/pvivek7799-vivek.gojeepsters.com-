<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


namespace Amasty\Finder\Model\ResourceModel;

use Amasty\Finder\Controller\Adminhtml\Finder\ImportUniversal;

class Finder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAX_LINE = 2000;
    const BATCH_SIZE = 1000;

    /**
     * @var \Amasty\Finder\Model\Finder\SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metaData;

    /**
     * Finder constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amasty\Finder\Model\Finder\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $metaData
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Finder\Model\Finder\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magento\Framework\App\ProductMetadataInterface $metaData,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->metaData = $metaData;
    }
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_finder_finder', 'finder_id');
    }

    /**
     * @param $finder
     * @param $file
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importUniversal($finder, $file)
    {
        $listErrors = [];
        $connection = $this->getConnection();
        $finderId = (int) $finder->getId();

        if (empty($file['name'])) {
            if ($finder->getData(ImportUniversal::IMPORTUNIVERSAL_CLEAR) && $finderId) {
                $connection->delete($this->getTable('amasty_finder_universal'), "finder_id = $finderId");
            }
            return $listErrors;
        }

        $fileName = $file['tmp_name'];
        $fileNamePart = pathinfo($file['name']);
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($fileName);
        } else {
            $mimeType = 'text/plain';
        }
        if ($fileNamePart['extension'] != 'csv' || $mimeType != 'text/plain') {
            throw new \Magento\Framework\Exception\LocalizedException(__('Incorrect file type. CSV needed'));
        }

        //for Mac OS
        ini_set('auto_detect_line_endings', 1);

        //file can be very big, so we read it by small chunks
        $file = fopen($fileName, 'r');
        if (!$file) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can not open file'));
        }

        if ($finder->getData(ImportUniversal::IMPORTUNIVERSAL_CLEAR) && $finderId) {
            $connection->delete($this->getTable('amasty_finder_universal'), "finder_id = $finderId");
        }

        while (($line = fgetcsv($file, self::MAX_LINE, ',', '"')) !== false) {
            foreach ($line as $sku) {
                $connection->insertOnDuplicate($this->getTable('amasty_finder_universal'), [
                    'finder_id' => $finderId,
                    'sku' => trim($sku, "\r\n\t' " . '"'),
                    'pid' => 0
                ]);
            }
        }

        $table1 = $this->getTable('amasty_finder_universal');
        $table2 = $this->getTable('catalog_product_entity');

        $connection->update(
            new \Zend_Db_Expr($table1 . ',' . $table2),
            ['pid' => new \Zend_Db_Expr($table2 . '.entity_id')],
            [$table1 . '.sku=' . $table2 . '.sku' => 0]
        );
        return $listErrors;
    }

    /**
     * @return array
     */
    public function updateLinks()
    {
        $connection = $this->getConnection();
        $table1 = $this->getTable('amasty_finder_map');
        $table2 = $this->getTable('catalog_product_entity');

        $connection->update(
            new \Zend_Db_Expr($table1 . ',' . $table2),
            ['pid' => new \Zend_Db_Expr($table2 . '.entity_id')],
            [$table1 . '.sku=' . $table2 . '.sku' => 0]
        );

        $sql = $connection->select()->from($table1, ['sku'])->where('pid=0')->limit(10);
        return $connection->fetchCol($sql);
    }

    /**
     * @param $collection
     * @param $valueId
     * @param $countEmptyDropdowns
     * @param $finderId
     * @param $isUniversal
     * @param $isUniversalLast
     * @return bool
     */
    public function addConditionToProductCollection(
        $collection,
        $valueId,
        $countEmptyDropdowns,
        $finderId,
        $isUniversal,
        $isUniversalLast
    ) {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $connection = $this->getConnection();

        $ids = [$valueId];
        for ($i = 0; $i < $countEmptyDropdowns; $i++) {
            $selectChild = $connection->select()
                ->from(['finderValue' => $collection->getTable('amasty_finder_value')], 'value_id')
                ->where('finderValue.parent_id IN (?)', $ids);
            $ids = $connection->fetchCol($selectChild);
        }

        $select = $collection->getSelect();

        if ($isUniversal) {
            // we need sub selects
            $univProducts = $connection->select()
                ->from(
                    ['finderUnivarsal' => $collection->getTable('amasty_finder_universal')],
                    ['sku']
                )
                ->where('finderUnivarsal.finder_id = ?', $finderId);

            $productIdsSelect = $connection->select()
                ->from(['finderMap' => $collection->getTable('amasty_finder_map')], ['sku'])
                ->where('finderMap.value_id IN (?)', $ids);

            $allProducts = $connection->select()->union([$univProducts, $productIdsSelect]);

            $query = $connection->select()->from($allProducts, ['sku']);

            $entityIds = $connection->fetchCol($query);

            if ($isUniversalLast) {
                $from = $select->getPart(\Magento\Framework\DB\Select::FROM);
                if (!isset($from['finderUnivarsal'])) {
                    $select->distinct()
                        ->joinLeft(
                            ['finderUnivarsal' => $collection->getTable('amasty_finder_universal')],
                            'finderUnivarsal.pid = e.entity_id',
                            []
                        )
                        ->order('IF(ISNULL(finderUnivarsal.pid), 0, 1)');
                }
            }
        } else {
            $entityIds = $connection->fetchCol($connection->select()
                ->from($collection->getTable('amasty_finder_map'), ['sku'])->where('value_id IN(?)', $ids));
        }

        $this->searchCriteriaBuilderFactory->get()
            ->addCollectionFilter($collection, 'sku', $entityIds);

        return true;
    }

    /**
     * @param $mapId
     * @return bool
     */
    public function isDeletable($mapId)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('amasty_finder_map');
        $selectSql = $connection->select()->from($table)->where('value_id = ?', $mapId);
        $result = $connection->fetchRow($selectSql);

        if (isset($result['value_id'])) {
            if ($result['value_id']) {
                return false;
            }
        }

        $table2 = $this->getTable('amasty_finder_value');
        $selectSql2 = $connection->select()->from($table2)->where('parent_id = ?', $mapId);

        $result2 = $connection->fetchRow($selectSql2);
        if (isset($result2['value_id'])) {
            if ($result2['value_id']) {
                return false;
            }
        }
        return true;
    }
}
