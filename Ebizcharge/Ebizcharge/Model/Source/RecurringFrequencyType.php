<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ebizcharge\Ebizcharge\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RecurringFrequencyType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            //['value' => 'once', 'label' => __('Once')],
            ['value' => 'daily', 'label' => __('Daily')],
            ['value' => 'weekly', 'label' => __('Weekly')],
            ['value' => 'bi-weekly', 'label' => __('Every two weeks')],
            ['value' => 'bi-monthly', 'label' => __('Twice per month')], // Twice per month on the 1 and 15th of every month
            ['value' => 'four-week', 'label' => __('Every four weeks')], // we can handle by monthly
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'two-month', 'label' => __('Every two months')],
            ['value' => 'quarterly', 'label' => __('Quarterly')],       // Every quarter on the 1st in Jan, Apr, Jul and Oct.
            ['value' => 'three-month', 'label' => __('Every three months')],
            ['value' => '90-days', 'label' => __('Every 90 days')],
            ['value' => 'four-month', 'label' => __('Every four months')],
            ['value' => 'five-month', 'label' => __('Every five months')],
            ['value' => 'six-month', 'label' => __('Every six months')],
            ['value' => '180-days', 'label' => __('Every 180 days')],
            ['value' => 'bi-annually', 'label' => __('Twice per year')], //Every 6 month on the 1st in Jan and Jul
            ['value' => 'annually', 'label' => __('Yearly')],
        ];
    }

    public function options()
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'bi-weekly' => 'Every two weeks',
            'bi-monthly' => 'Twice per month',
            'four-week' => 'Every four weeks',
            'monthly' => 'Monthly',
            'two-month' => 'Every two months',
            'quarterly' => 'Quarterly',
            'three-month' => 'Every three months',
            '90-days' => 'Every 90 days',
            'four-month' => 'Every four months',
            'five-month' => 'Every five months',
            'six-month' => 'Every six months',
            '180-days' => 'Every 180 days',
            'bi-annually' => 'Twice per year',
            'annually' => 'Annually',
        ];
    }
}
