<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Payment method available action types
 *
 * Class PaymentAction
 * @package Ebizcharge\Ebizcharge\Model\Source
 */
class PaymentAction implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'authorize',
                'label' => __('Authorize Only'),
            ],
            [
                'value' => 'authorize_capture',
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
