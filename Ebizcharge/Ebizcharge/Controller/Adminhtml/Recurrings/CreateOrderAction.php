<?php
/**
 * Create the recurring orders
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Ebizcharge\Ebizcharge\Model\CreateOrder;

class CreateOrderAction extends Action
{
    private $fkValidator;
    protected $tranApi;
    protected $config;
    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @param CreateOrder $createOrder
     * @param Context $context
     * @param Validator $fkValidator
     * @param Config $config
     * @param TranApi $tranApi
     */
    public function __construct(
        CreateOrder $createOrder,
        Context $context,
        Validator $fkValidator,
        Config $config,
        TranApi $tranApi
    )
    {
        parent::__construct($context);
        $this->fkValidator = $fkValidator;
        $this->config = $config;
        $this->tranApi = $tranApi;
        $this->createOrder = $createOrder;
    }

    /**
     * @return bool
     * @throws NotFoundException
     */
    public function execute()
    {
        $request = $this->_request;

        if ($this->config->isEbizchargeActive() == 0) {
            return $this->createErrorResponse('Failed: EBizCharge module is inactive.');
        }

        if ($this->config->isRecurringEnabled() == 0) {
            return $this->createErrorResponse('Failed: EBizCharge recurring functionality is inactive.');
        }

        $post = $request->getPost();
        $startDate = $post['start_date'];

        $this->tranApi->cronlog('CreateOrder run start. The time is ' . date("Y-m-d h:i:sa"));

        $this->createOrder->checkRecurringOrders(date_create($startDate));
        echo '1';
        exit;
    }

    /**
     * Creates an error message, and passes it to the "Manage
     *
     * @param $errorMessage
     * @return mixed
     */
    private function createErrorResponse($errorMessage)
    {
        $this->messageManager->addErrorMessage($errorMessage);
        return true;
    }

}
