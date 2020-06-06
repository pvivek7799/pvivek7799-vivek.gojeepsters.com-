<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Block;

/**
 * Class Ajax
 * @package Aheadworks\AdvancedReports\Block
 */
class Ajax extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'url' => $this->getUrl(
                'aw_advancedreports/countViews/product/',
                [
                    '_current' => true,
                    '_secure' => $this->templateContext->getRequest()->isSecure()
                ]
            ),
        ];
        return json_encode($params);
    }
}
