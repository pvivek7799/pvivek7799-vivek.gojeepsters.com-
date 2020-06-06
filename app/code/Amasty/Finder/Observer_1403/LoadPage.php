<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


namespace Amasty\Finder\Observer;

use Amasty\Finder\Model\Finder;

class LoadPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amasty\Finder\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Finder\Helper\Url
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Amasty\Finder\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * LoadPage constructor.
     * @param \Amasty\Finder\Model\Session $session
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Amasty\Finder\Helper\Url $urlHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Amasty\Finder\Helper\Config $configHelper
     */
    public function __construct(
        \Amasty\Finder\Model\Session $session,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \Amasty\Finder\Helper\Url $urlHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Amasty\Finder\Helper\Config $configHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->session = $session;
        $this->request = $request;
        $this->response = $response;
        $this->urlHelper = $urlHelper;
        $this->urlInterface = $urlInterface;
        $this->configHelper = $configHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/paramFind.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
        $html = $observer->getResponse()->getContent();
        $request = $observer->getRequest();
        if ($request->isAjax()) {
            return $observer;
        }
        $activeFinders = $this->session->getAmfinderSavedValues()
            ?: $this->session->getAllFindersData();
			setcookie("find", "", time()-1); 

			$request->setParam('find','');
        if (!$activeFinders || $request->getParam('find') !== null) {
		
		$logger->info('loadpage.php called:');
		//$this->session->setData(null);
            return $observer;
        }

        $currentUrlWithoutGet = $this->urlBuilder->getUrl('*/*/*', ['_use_rewrite' => true]);

        foreach ($activeFinders as $finderId => $values) {
            $finderExist = strpos($html, 'amfinder_' . $finderId) !== false;
            if ($finderExist) {
				$logger->info('finderExists called:');
                if (!$this->configHelper->getConfigValue('general/category_search')
                    || ($this->configHelper->getConfigValue('general/category_search')
                        && in_array($currentUrlWithoutGet, $values['apply_url'])
                        && strpos($request->getRequestUri(), $values['url_param']) === false
                        && !$this->urlHelper->hasFinderParamInUri($request->getRequestUri())
                    )
                ) {
				$logger->info('currentUrlWithoutGet called:');
                    $observer->getResponse()->setRedirect($this->urlHelper->getUrlWithFinderParam(
                        $this->urlInterface->getCurrentUrl(),
                        $values['url_param']
                    ));
                    break;
                }
            }
        }

        return $observer;
    }
}
