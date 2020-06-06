<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\Period;

use Aheadworks\AdvancedReports\Model\Source\Period as PeriodRangeSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ListsInterface as LocaleLists;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Aheadworks\AdvancedReports\Model\Config;

/**
 * Class RangeResolver
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\Period
 */
class RangeResolver
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var LocaleLists
     */
    private $localeList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param TimezoneInterface $localeDate
     * @param LocaleLists $localeList
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     */
    public function __construct(
        TimezoneInterface $localeDate,
        LocaleLists $localeList,
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->localeDate = $localeDate;
        $this->localeList = $localeList;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Resolve date range dates
     *
     * @param string $range
     *
     * @return array
     */
    public function resolve($range)
    {
        $timezone = $this->localeDate->getConfigTimezone(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        switch ($range) {
            case PeriodRangeSource::TYPE_TODAY:
                return $this->getToday($timezone);

            case PeriodRangeSource::TYPE_YESTERDAY:
                return $this->getYesterday($timezone);

            case PeriodRangeSource::TYPE_WEEK_TO_DATE:
                return $this->getWeekToDate($timezone);

            case PeriodRangeSource::TYPE_LAST_7_DAYS:
                return $this->getLastSevenDays($timezone);

            case PeriodRangeSource::TYPE_LAST_WEEK:
                return $this->getLastWeek($timezone);

            case PeriodRangeSource::TYPE_LAST_BUSINESS_WEEK:
                return $this->getLastBusinessWeek($timezone);

            case PeriodRangeSource::TYPE_MONTH_TO_DATE:
                return $this->getMonthToDateRange($timezone);

            case PeriodRangeSource::TYPE_LAST_MONTH:
                return $this->getLastMonth($timezone);
        }

        return [];
    }

    /**
     * Retrieve today date range
     *
     * @param string $timezone
     * @return array
     */
    private function getToday($timezone)
    {
        $from = $this->prepareDate(new \DateTime('today', new \DateTimeZone($timezone)), $timezone);
        $to = $this->prepareDate(new \DateTime('today', new \DateTimeZone($timezone)), $timezone);
        $cFrom = $this->prepareDate(new \DateTime('yesterday', new \DateTimeZone($timezone)), $timezone);
        $cTo = $this->prepareDate(new \DateTime('yesterday', new \DateTimeZone($timezone)), $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve yesterday date range
     *
     * @param string $timezone
     * @return array
     */
    private function getYesterday($timezone)
    {
        $from = $this->prepareDate(new \DateTime('yesterday', new \DateTimeZone($timezone)), $timezone);
        $to = $this->prepareDate(new \DateTime('yesterday', new \DateTimeZone($timezone)), $timezone);
        $cFrom = $this->prepareDate(new \DateTime('2 days ago', new \DateTimeZone($timezone)), $timezone);
        $cTo = $this->prepareDate(new \DateTime('2 days ago', new \DateTimeZone($timezone)), $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve week to date range
     *
     * @param string $timezone
     * @return array
     */
    private function getWeekToDate($timezone)
    {
        $firstDayOfWeek = $this->scopeConfig->getValue('general/locale/firstday');
        $firstWeekDayName = $this->getWeekdayKeyByNum($firstDayOfWeek);

        $fromToday = new \DateTime('today', new \DateTimeZone($timezone));
        $currentDayOfWeek = $fromToday->format('w');
        $from = $firstDayOfWeek == $currentDayOfWeek
            ? $this->prepareDate($fromToday, $timezone)
            : $this->prepareDate(new \DateTime('last ' . $firstWeekDayName, new \DateTimeZone($timezone)), $timezone);
        $to = $this->prepareDate(new \DateTime('now', new \DateTimeZone($timezone)), $timezone);

        $cFrom = clone $from;
        $cFrom->modify('-7 days');
        $cTo = clone $to;
        $cTo->modify('-7 days');
        $cFrom = $this->prepareDate($cFrom, $timezone);
        $cTo = $this->prepareDate($cTo, $timezone);

        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve last 7 days range range
     *
     * @param string $timezone
     * @return array
     */
    private function getLastSevenDays($timezone)
    {
        $from = $this->prepareDate(new \DateTime('6 days ago', new \DateTimeZone($timezone)), $timezone);
        $to = $this->prepareDate(new \DateTime('now', new \DateTimeZone($timezone)), $timezone);
        $cFrom = $this->prepareDate(new \DateTime('13 days ago', new \DateTimeZone($timezone)), $timezone);
        $cTo = $this->prepareDate(new \DateTime('7 days ago', new \DateTimeZone($timezone)), $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve last week range
     *
     * @param string $timezone
     * @return array
     */
    private function getLastWeek($timezone)
    {
        $firstDayOfWeek = $this->scopeConfig->getValue('general/locale/firstday');
        $lastDayOfWeek = $firstDayOfWeek + 6 > 6
            ? 0
            : $firstDayOfWeek + 6;
        $from = new \DateTime(
            'previous ' . $this->getWeekdayKeyByNum($firstDayOfWeek),
            new \DateTimeZone($timezone)
        );
        $to = new \DateTime(
            'previous ' . $this->getWeekdayKeyByNum($lastDayOfWeek),
            new \DateTimeZone($timezone)
        );
        if ($to->getTimestamp() < $from->getTimestamp()) {
            $from->modify('-7 days');
        }
        $cFrom = clone $from;
        $cFrom->modify('-7 days');
        $cTo = clone $to;
        $cTo->modify('-7 days');

        $from = $this->prepareDate($from, $timezone);
        $to = $this->prepareDate($to, $timezone);
        $cFrom = $this->prepareDate($cFrom, $timezone);
        $cTo = $this->prepareDate($cTo, $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve last business week range
     *
     * @param string $timezone
     * @return array
     */
    private function getLastBusinessWeek($timezone)
    {
        $businessWeekdays = $this->getBusinessWeekdays();
        $firstBusinessDayOfWeek = reset($businessWeekdays);
        $lastBusinessDyaOfWeek = end($businessWeekdays);
        $from = new \DateTime(
            'previous ' . $this->getWeekdayKeyByNum($firstBusinessDayOfWeek),
            new \DateTimeZone($timezone)
        );
        $to = new \DateTime(
            'previous ' . $this->getWeekdayKeyByNum($lastBusinessDyaOfWeek),
            new \DateTimeZone($timezone)
        );
        if ($to->getTimestamp() < $from->getTimestamp()) {
            $from->modify('-7 days');
        }
        $cFrom = clone $from;
        $cFrom->modify('-7 days');
        $cTo = clone $to;
        $cTo->modify('-7 days');

        $from = $this->prepareDate($from, $timezone);
        $to = $this->prepareDate($to, $timezone);
        $cFrom = $this->prepareDate($cFrom, $timezone);
        $cTo = $this->prepareDate($cTo, $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve month to date range
     *
     * @param string $timezone
     *
     * @return array
     */
    private function getMonthToDateRange($timezone)
    {
        $now = new \DateTime('now', new \DateTimeZone($timezone));
        $nowModify = new \DateTime('now', new \DateTimeZone($timezone));
        $nowModify->modify('-1 month');
        $lastDayOfThisMonth = new \DateTime('last day of this month', new \DateTimeZone($timezone));
        $firstDayOfThisMonth = new \DateTime('first day of this month', new \DateTimeZone($timezone));
        $firstDayOfPreviousMonth = new \DateTime('first day of last month', new \DateTimeZone($timezone));
        $lastDayOfPreviousMonth = $now == $lastDayOfThisMonth
            ? new \DateTime('last day of last month', new \DateTimeZone($timezone))
            : $nowModify;

        $from = $this->prepareDate($firstDayOfThisMonth, $timezone);
        $to = $this->prepareDate($now, $timezone);
        $cFrom = $this->prepareDate($firstDayOfPreviousMonth, $timezone);
        $cTo = $this->prepareDate($lastDayOfPreviousMonth, $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Retrieve last month range
     *
     * @param string $timezone
     * @return array
     */
    private function getLastMonth($timezone)
    {
        $from = $this->prepareDate(new \DateTime('first day of last month', new \DateTimeZone($timezone)), $timezone);
        $to = $this->prepareDate(new \DateTime('last day of last month', new \DateTimeZone($timezone)), $timezone);
        $cFrom = $this->prepareDate(new \DateTime('first day of 2 month ago', new \DateTimeZone($timezone)), $timezone);
        $cTo = $this->prepareDate(new \DateTime('last day of 2 month ago', new \DateTimeZone($timezone)), $timezone);
        list($cYearFrom, $cYearTo) = $this->getPreviousYearDates($from, $to);

        return [
            'from'   => $from,
            'to'     => $to,
            'c_from' => $cFrom,
            'c_to'   => $cTo,
            'c_year_from' => $cYearFrom,
            'c_year_to'   => $cYearTo,
        ];
    }

    /**
     * Get weekday key by number
     *
     * @param int $index
     *
     * @return string|null
     */
    private function getWeekdayKeyByNum($index)
    {
        $days = [
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        ];
        return isset($days[$index]) ? $days[$index] : null;
    }

    /**
     * Get weekdays keyed by index
     *
     * @return array
     */
    private function getWeekdaysKeyedByIndex()
    {
        $weekdaysKeyedByIndex = [];
        foreach ($this->localeList->getOptionWeekdays() as $weekday) {
            $weekdaysKeyedByIndex[$weekday['value']] = $weekday['label'];
        }
        return $weekdaysKeyedByIndex;
    }

    /**
     * Get business weekdays
     *
     * @return array
     */
    private function getBusinessWeekdays()
    {
        $weekdays = array_keys($this->getWeekdaysKeyedByIndex());
        $weekendDays = explode(',', $this->scopeConfig->getValue('general/locale/weekend'));
        return array_diff($weekdays, $weekendDays);
    }

    /**
     * Prepare date
     *
     * @param \DateTime $date
     * @param string $timezone
     * @return \DateTime
     */
    private function prepareDate($date, $timezone)
    {
        $firstAvailableDate = new \DateTime(
            $this->config->getFirstAvailableDate(),
            new \DateTimeZone($timezone)
        );

        if ($date > $firstAvailableDate) {
            return $date;
        }

        return $firstAvailableDate;
    }

    /**
     * Retrieve dates values minus one year
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    private function getPreviousYearDates($from, $to)
    {
        $cFrom = clone $from;
        $cTo = clone $to;
        $cFrom->modify('-1 year');
        $cTo->modify('-1 year');

        return [$cFrom, $cTo];
    }
}
