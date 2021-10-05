<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/8/21
 * Time: 2:19 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Recurrion Frequency Class
 *
 * Class Frequency
 */
class Frequency implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label'     =>      'Daily',
                'value'     =>      'daily'
            ],
            [
                'label'     =>      'Monthly',
                'value'     =>      'monthly'
            ],            [
                'label'     =>      'Bi-monthly',
                'value'     =>      'bi-monthly'
            ],
            [
                'label'     =>      'Quarterly',
                'value'     =>      'quarterly'
            ],
            [
                'label'     =>      'Four-month',
                'value'     =>      'four-month'
            ],
            [
                'label'     =>      'Five-month',
                'value'     =>      'five-month'
            ],            [
                'label'     =>      'Bi-annually',
                'value'     =>      'bi-annually'
            ],
            [
                'label'     =>      'Annually',
                'value'     =>      'annually'
            ]
        ];
    }
}
