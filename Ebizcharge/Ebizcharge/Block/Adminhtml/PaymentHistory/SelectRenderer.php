<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/15/21
 * Time: 3:34 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;
use Magento\Framework\DataObject;

/**
 * Select options for Action column
 *
 * Class SelectRenderer
 */
class SelectRenderer extends Select
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        $refNum = $row->getData('refNum');
        $customerEmail = $row->getData('customerEmail');
        return '<select name="actions" class="print-actions admin__control-select">
                    <option value="">Select</option>
                    <option class="print_receipt" value="' . $refNum . '" data-action="print_receipt">Print Receipt</option>
                    <option class="print_email" data-action="print_email" data-refNum="' . $refNum . '" 
                            data-email="' . $customerEmail . '" value="' . $refNum . '">Email Receipt
                    </option>
                </select>';
    }

}
