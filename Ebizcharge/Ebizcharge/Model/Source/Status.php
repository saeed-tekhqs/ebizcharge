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
 * Recurrion Status Class
 *
 * Class Status
 */
class Status implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label'     =>      'On',
                'value'     =>      0
            ],
            [
                'label'     =>      'Off',
                'value'     =>      1
            ],
            [
                'label'     =>      'Deleted',
                'value'     =>      3
            ]
        ];
    }
}
