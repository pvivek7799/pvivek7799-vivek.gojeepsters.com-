<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


namespace Amasty\Finder\Plugin\PageCache\Model\Varnish;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\PageCache\Exception\UnsupportedVarnishVersion;
use Magento\PageCache\Model\Varnish\VclTemplateLocator as MagentoVclTplLocator;

class VclTemplateLocator
{
    /**
     * @var array
     */
    private $supportedVarnishVersions = [
        MagentoVclTplLocator::VARNISH_SUPPORTED_VERSION_4 => MagentoVclTplLocator::VARNISH_4_CONFIGURATION_PATH,
        MagentoVclTplLocator::VARNISH_SUPPORTED_VERSION_5 => MagentoVclTplLocator::VARNISH_5_CONFIGURATION_PATH,
    ];

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(Reader $reader, ReadFactory $readFactory, ScopeConfigInterface $scopeConfig)
    {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MagentoVclTplLocator $subject
     * @param callable $proceed
     * @param string $version
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTemplate(
        MagentoVclTplLocator $subject,
        callable $proceed,
        $version
    ) {
        $moduleEtcPath = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Amasty_Finder');
        $configFilePath = $moduleEtcPath . '/' . $this->scopeConfig->getValue($this->getVclTemplatePath($version));
        $directoryRead = $this->readFactory->create($moduleEtcPath);
        $configFilePath = $directoryRead->getRelativePath($configFilePath);
        $template = $directoryRead->readFile($configFilePath);
        return $template;
    }

    /**
     * Get Vcl template path
     *
     * @param int $version Varnish version
     * @return string
     * @throws UnsupportedVarnishVersion
     */
    private function getVclTemplatePath($version)
    {
        if (!isset($this->supportedVarnishVersions[$version])) {
            throw new UnsupportedVarnishVersion(__('Unsupported varnish version'));
        }

        return $this->supportedVarnishVersions[$version];
    }
}
