<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

class HiddenDependents
{
    /**
     * @var array
     */
    protected $quoteItemsHiddenDependents = [];

    /**
     * Get quote items hidden dependents
     *
     * @return array
     */
    public function getQuoteItemsHiddenDependents()
    {
        return $this->quoteItemsHiddenDependents;
    }

    /**
     * Set quote items hidden dependents
     *
     * @param array
     * @return void
     */
    public function setQuoteItemsHiddenDependents($data)
    {
        $this->quoteItemsHiddenDependents = $data;
    }
}