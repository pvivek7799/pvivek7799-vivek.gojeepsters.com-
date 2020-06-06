<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model;

/**
 * Class ResultService
 * @package MW\Onestepcheckout\Model
 */
class ResultService implements \MW\Onestepcheckout\Api\ResultServiceInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * ResultService constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function createRaw()
    {
        return $this->resultRawFactory->create();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function createJson()
    {
        return $this->resultJsonFactory->create();
    }
}
