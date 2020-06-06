<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Indexer;

/**
 * Class Statistics
 *
 * @package Aheadworks\AdvancedReports\Model\Indexer
 */
class Statistics implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Statistics\Action\Full
     */
    private $statisticsIndexerFull;

    /**
     * @param Statistics\Action\Full $statisticsIndexerFull
     */
    public function __construct(
        Statistics\Action\Full $statisticsIndexerFull
    ) {
        $this->statisticsIndexerFull = $statisticsIndexerFull;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->statisticsIndexerFull->execute();
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids)
    {
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeList(array $ids)
    {
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRow($id)
    {
    }
}
