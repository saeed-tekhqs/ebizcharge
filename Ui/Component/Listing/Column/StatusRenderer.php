<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/18/21
 * Time: 9:58 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class StatusRenderer
 */
class StatusRenderer extends Column
{
    /**
     * recurring order status
     */
    const STATUS_ON = 0;
    const STATUS_OFF = 1;
    const STATUS_DELETED = 3;

    /**
     * @param array $dataSource
     * @return array`
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $status = $this->getData('name');
                foreach ($dataSource['data']['items'] as & $item) {
                    if (isset($item[$status])) {
                        $item[$status] = $this->getStatusHtml((int)$item[$status]);
                    }
                }
        }
        return $dataSource;
    }

    /**
     * Get status html as per status code
     *
     * @param int $status
     * @return string
     */
    private function getStatusHtml(int $status): string
    {
        $class = 'grid-severity-critical';
        $label = __('Not found');
        switch ($status) {
            case static::STATUS_ON:
                $class = 'grid-severity-notice';
                $label = __('On');
                break;
            case static::STATUS_OFF:
                $class = 'grid-severity-minor';
                $label = __('Off');
                break;
            case static::STATUS_DELETED:
                $class = 'grid-severity-critical';
                $label = __('Deleted');
                break;
        }
        return '<span class="' . $class . '"><span>' . $label . '</span></span>';
    }

}
