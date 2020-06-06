<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MetadataPool
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider
 */
class MetadataPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @var MetadataInterface[]
     */
    private $metadataInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $metadata
     */
    public function __construct(ObjectManagerInterface $objectManager, $metadata = [])
    {
        $this->objectManager = $objectManager;
        $this->metadata = $metadata;
    }

    /**
     * Retrieves metadata for engine code
     *
     * @param string $dataSourceName
     * @return MetadataInterface
     * @throws \Exception
     */
    public function getMetadata($dataSourceName)
    {
        if (!isset($this->metadataInstances[$dataSourceName])) {
            if (!isset($this->metadata[$dataSourceName])) {
                throw new \Exception(sprintf('Unknown data provider metadata: %s requested', $dataSourceName));
            }
            $this->metadataInstances[$dataSourceName] = $this->objectManager->create(
                MetadataInterface::class,
                ['data' => $this->metadata[$dataSourceName]]
            );
        }
        return $this->metadataInstances[$dataSourceName];
    }
}
