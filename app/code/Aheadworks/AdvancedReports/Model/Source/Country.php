<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;

/**
 * Class Country
 *
 * @package Aheadworks\AdvancedReports\Model\Source
 */
class Country implements OptionSourceInterface
{
    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * Options array
     *
     * @var []
     */
    private $options;

    /**
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    public function __construct(
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $countries = $this->countryInformationAcquirer->getCountriesInfo();
            /** @var CountryInformationInterface $countryData */
            foreach ($countries as $countryData) {
                $this->options[] = [
                    'value' => $countryData->getId(),
                    'label' => $countryData->getFullNameLocale()
                ];
            }
        }
        return $this->options;
    }

    /**
     * Get options
     *
     * @return []
     */
    public function getOptions()
    {
        $options = $this->toOptionArray();
        $result = [];

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
