<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Api;

/**
 * Interface ResultServiceInterface
 * @package MW\Onestepcheckout\Api
 */
interface ResultServiceInterface
{
    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function createRaw();

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function createJson();
}
