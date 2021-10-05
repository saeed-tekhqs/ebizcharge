<?php
declare(strict_types=1);
/**
 * Payment Items Method Source Model
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Whether you use Magento as your source for uploading or downloading items
 *
 * Class ItemsMethod
 * @package Ebizcharge\Ebizcharge\Model\Config
 */
class ItemsMethod implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => 'Upload',
                'value' => 'upload'
            ],
            [
                'label' => 'Download',
                'value' => 'download'
            ]
        ];


    }

}
