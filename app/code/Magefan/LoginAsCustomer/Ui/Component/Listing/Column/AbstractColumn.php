<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

abstract class AbstractColumn extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    protected $sourceColumnName;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            $hidden = !$this->_authorization->isAllowed('Magefan_LoginAsCustomer::login_button');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item[$this->sourceColumnName])) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'loginascustomer/login/login',
                            ['customer_id' => $item[$this->sourceColumnName]]
                        ),
                        'label' => __('Login As Customer'),
                        'hidden' => $hidden,
                        'target' => '_blank',
                    ];
                } elseif (\Magefan\Community\Model\UrlChecker::showUrl($this->urlBuilder->getCurrentUrl())) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'loginascustomer/guest/convert',
                            ['order_id' => $item['entity_id']]
                        ),
                        'label' => __('Convert Guest to Customer'),
                        'hidden' => $hidden,
                        'target' => '_blank',
                    ];
                }
            }
        }

        return $dataSource;
    }
}
