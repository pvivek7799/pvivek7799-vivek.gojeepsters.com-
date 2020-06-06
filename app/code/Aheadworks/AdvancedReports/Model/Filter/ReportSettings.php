<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Filter;

use Magento\Framework\App\RequestInterface;

/**
 * Class ReportSettings
 *
 * @package Aheadworks\AdvancedReports\Model\Filter
 */
class ReportSettings implements FilterInterface
{
    /**
     * @var string
     */
    const REPORT_SETTINGS_PARAM = 'report_settings';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array|null
     */
    private $reportSettings;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (null === $this->reportSettings) {
            $this->reportSettings = [];

            $reportSettingParams = $this->request->getParam(self::REPORT_SETTINGS_PARAM);
            if (empty($reportSettingParams) || !is_array($reportSettingParams)) {
                return $this->reportSettings;
            }

            foreach ($reportSettingParams as $name => $value) {
                $this->reportSettings[$name] = $value;
            }
        }

        return $this->reportSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return [];
    }

    /**
     * Retrieve report setting param
     *
     * @param string $param
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getReportSettingParam($param, $default = null)
    {
        $reportSettings = $this->getValue();
        if (isset($reportSettings[$param])) {
            return $reportSettings[$param];
        }

        return $default;
    }
}
