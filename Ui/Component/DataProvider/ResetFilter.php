<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/18/21
 * Time: 2:42 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui\Component\DataProvider;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as CoreDataProvider;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class DataProvider
 */
class ResetFilter extends CoreDataProvider
{
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() == static::CUSTOMER_NAME) {
            $filter = $this->resetFilter($filter);
        }
        parent::addFilter($filter);
    }

    /**
     * This function reset the condition for customer ID and customer name
     * @param Filter $filter
     * @return Filter
     */
    private function resetFilter(Filter $filter): Filter
    {
        $value = trim($filter->getValue(), '%');
        if (is_numeric($value)) {
            $filter->setField(RecurringInterface::MAGE_CUST_ID);
        } elseif (strpos($value, '@') !== false){
            $filter->setField(static::CUSTOMER_EMAIL);
        }
        return $filter;
    }

}
