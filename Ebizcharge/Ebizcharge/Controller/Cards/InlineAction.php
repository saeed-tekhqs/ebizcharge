<?php
/**
 * Deletes the customer's saved payment method during the checkout process.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Cards inline editing
 *
 * Class InlineAction
 */
class InlineAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @param TranApi $tranApi
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->tranApi = $tranApi;
    }

    /**
     * @return false|Redirect
     */
    public function execute()
    {
        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');

        if ($cid === null || $mid === null) {
            return false;
        }

        try {
            $params = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerToken' => $cid,
                'paymentMethodId' => $mid,
            );

            $this->tranApi->getClient()->deleteCustomerPaymentMethodProfile($params);
            /*if ($client->deleteCustomerPaymentMethodProfile($params)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment Method deleted successfully'));
            }*/
        } catch (\Exception $e) {
            return false;
        }
        return $this->redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }
}
