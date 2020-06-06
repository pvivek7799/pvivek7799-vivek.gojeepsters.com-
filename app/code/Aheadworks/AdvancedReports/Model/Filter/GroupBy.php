<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Aheadworks\AdvancedReports\Model\Source\Groupby as GroupbySource;

/**
 * Class GroupBy
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
class GroupBy implements FilterInterface
{
    /**
     * @var string
     */
    const SESSION_KEY = 'aw_arep_groupby';

    /**
     * @var string
     */
    private $groupBy;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @param RequestInterface $request
     * @param SessionManagerInterface $session
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $session
    ) {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!$this->groupBy) {
            $this->groupBy = $this->request->getParam('group_by');
            if (!$this->groupBy) {
                $this->groupBy = $this->session->getData(self::SESSION_KEY);
            }
            if (!$this->groupBy) {
                $this->groupBy = $this->getDefaultValue();
            }
            $this->session->setData(self::SESSION_KEY, $this->groupBy);
        }
        return $this->groupBy;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return GroupbySource::TYPE_MONTH;
    }
}
