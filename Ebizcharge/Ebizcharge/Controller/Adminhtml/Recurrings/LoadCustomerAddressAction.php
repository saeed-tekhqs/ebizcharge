<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\Data as CustomerAddress;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Load customer addresses
 *
 * Class LoadCustomerAddressAction
 */
class LoadCustomerAddressAction extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var CustomerAddress
     */
    private $customerAddress;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerAddress $customerAddress
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerAddress $customerAddress
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerAddress = $customerAddress;
    }

    /**
     * @return false|Json
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getPostValue('customer_id');
        if ($customerId) {
            $addresses = $this->customerAddress->getCustomerAddressList($customerId);
            return $this->resultJsonFactory->create()
                ->setData(['html_data' => $addresses]);
        }
        return $this->resultJsonFactory->create()
            ->setData(['html_data' => null]);
    }

}
