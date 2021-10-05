<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/21/21
 * Time: 5:16 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\text;
use Magento\Framework\DataObject;


/**
 * This class adds css classes to result column as per result type
 *
 * Class ResultRenderer
 */
class ResultRenderer extends text
{
    const ADMIN_GRID_CLASS = 'history';

    /**
     * recurring order status
     */
    const RESULT_APPROVED = 'Approved';
    const RESULT_DECLINED = 'Declined';
    const RESULT_REJECTED = 'Rejected';

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        return $this->getStatusHtml($row->getData('resultStatus'));

    }

    /**
     * Get result html as per result code
     *
     * @param string $resultCode
     * @return string
     */
    private function getStatusHtml(string $resultCode): string
    {
        $class = 'grid-severity-critical';
        $label = __('Not found');
        switch ($resultCode) {
            case static::RESULT_APPROVED:
                $class = 'grid-severity-notice';
                $label = __('Approved');
                break;
            case static::RESULT_DECLINED:
                $class = 'grid-severity-minor';
                $label = __('Declined');
                break;
            case static::RESULT_REJECTED:
                $class = 'grid-severity-critical';
                $label = __('Rejected');
                break;
        }

        $actionName = $this->getRequest()->getActionName();
        return $actionName == static::ADMIN_GRID_CLASS ? '<span class="' . $class . '"><span>' . $label . '</span></span>' : (string)$label;
    }

}
