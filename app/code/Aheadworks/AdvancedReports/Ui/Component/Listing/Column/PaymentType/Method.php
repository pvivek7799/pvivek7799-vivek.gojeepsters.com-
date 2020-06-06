<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing\Column\PaymentType;

use Aheadworks\AdvancedReports\Model\Url as UrlModel;
use Aheadworks\AdvancedReports\Model\Serializer;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Method
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing\Column\PaymentType
 */
class Method extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var UrlModel
     */
    private $urlModel;

    /**
     * @var Serializer;
     */
    private $serializer;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlModel $urlModel
     * @param Serializer $serializer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlModel $urlModel,
        Serializer $serializer,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlModel = $urlModel;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $methodName = $item['method'];
                if (isset($item['additional_info']) && $item['additional_info']) {
                    $additionalInfo = $this->serializer->unserialize($item['additional_info']);
                    if (isset($additionalInfo['method_title'])) {
                        $methodName = $additionalInfo['method_title'];
                    }
                }

                $params = [
                    'payment_type' => $item['method'],
                    'payment_name' => base64_encode($methodName)
                ];
                $item['row_url'] = $this->urlModel->getUrl(
                    'paymenttype',
                    'salesoverview',
                    $dataSource['data']['periodFromFilter'],
                    $dataSource['data']['periodToFilter'],
                    $params
                );
                $item['row_label'] = $methodName . ' (' . $item['method'] . ')';
            }
        }
        return $dataSource;
    }
}
