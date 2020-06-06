<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Range
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
class Range implements FilterInterface
{
    /**
     * @var string
     */
    const SESSION_RANGE_FROM_KEY = 'aw_rep_range_from_key';

    /**
     * @var string
     */
    const SESSION_RANGE_TO_KEY = 'aw_rep_range_to_key';

    /**
     * @var array
     */
    private $rangeCache;

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
        if (null == $this->rangeCache) {
            $from = $this->request->getParam('range_from');
            $to = $this->request->getParam('range_to');
            if ($from == null && $to == null) {
                $from = $this->session->getData(self::SESSION_RANGE_FROM_KEY);
                $to = $this->session->getData(self::SESSION_RANGE_TO_KEY);
                if ($from == null && $to == null) {
                    $this->rangeCache = null;
                    return $this->rangeCache;
                }
            }
            $this->session->setData(self::SESSION_RANGE_FROM_KEY, $from);
            $this->session->setData(self::SESSION_RANGE_TO_KEY, $to);
            $this->rangeCache = [
                'from' => $from,
                'to'   => $to,
            ];
        }
        return $this->rangeCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return [
            'from' => null,
            'to' => null,
        ];
    }
}
