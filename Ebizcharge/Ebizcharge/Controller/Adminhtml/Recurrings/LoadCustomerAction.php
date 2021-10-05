<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\UrlInterface;

class LoadCustomerAction extends Action implements HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    private $errorsMap = [];
    private $jsonFactory;
    private $fkValidator;
    private $token;
    protected $_tran;
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param Token $token
     * @param TranApi $tranApi
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        Validator $fkValidator,
        ScopeConfigInterface $scopeConfig,
        Token $token,
        TranApi $tranApi,
        ResponseFactory $responseFactory,
        UrlInterface $url
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->fkValidator = $fkValidator;
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->_scopeConfig = $scopeConfig;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();
        $customerId = isset($post['customer_id']) ? $post['customer_id'] : '';
        $ebizCustomerId = $this->_tran->getCustomerToken($customerId);

        if (!empty($ebizCustomerId)) {
            try {

                $paymentMethods = $this->_tran->getCustomerPaymentMethods($ebizCustomerId);

                $method = "";

                foreach ($paymentMethods as $paymentMethod) {
                    $method .= "<option value='" . $paymentMethod->MethodID . "'>" . $paymentMethod->MethodName . "</option>";
                }

                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $method]);

            } catch (\Exception $ex) {
                $method = "<option value=''>No payment method found</option>";
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $method]);
            }
        } else {
            //return [];
            $method = "<option value=''>Invalid Customer ID</option>";
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $method]);
        }
    }

}
