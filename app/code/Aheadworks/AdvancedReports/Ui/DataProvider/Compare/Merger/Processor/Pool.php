<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Pool
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Processor
 */
class Pool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MergerInterface[]
     */
    private $mergers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $mergers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $mergers = []
    ) {
        $this->objectManager = $objectManager;
        $this->mergers = $mergers;
    }

    /**
     * Retrieve merger by name
     *
     * @param string $mergerName
     * @param array|null $config
     * @return MergerInterface
     * @throws \Exception
     */
    public function getMerger($mergerName, $config = [])
    {
        if (!isset($this->mergers[$mergerName])) {
            throw new \Exception(sprintf('Unknown merger: %s requested', $mergerName));
        }
        $arguments = !empty($config) ? ['data' => $config] : [];
        $merger = $this->objectManager->create($this->mergers[$mergerName], $arguments);

        return $merger;
    }
}
