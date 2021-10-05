<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/17/21
 * Time: 10:14 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * This class update subscription statuses , 0=Active | 1=Suspended | 2=Expired | 3=Canceled
 *
 * Class UpdateSubscriptionStatus
 * @package Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings
 */
class UpdateSubscriptionStatus extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    public function __construct(
        Context $context,
        TranApi $tranApi,
        Validator $fkValidator,
        RecurringRepositoryInterface $recurringRepository
    ) {
        parent::__construct($context);
        $this->tranApi = $tranApi;
        $this->recurringRepository = $recurringRepository;
        $this->fkValidator = $fkValidator;
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Action failure. Please try again.')
        ];
    }

    /**
     *
     */
    public function execute()
    {
        if(!$this->_request instanceof Http) {
            $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if(!$this->fkValidator->validate($this->_request)) {
            $this->createErrorResponse(self::WRONG_TOKEN);
        }

        $mid = $this->getRequest()->getParam('mid');
        $sid = $this->getRequest()->getParam('sid');
        $status = $this->getRequest()->getParam('actionName') == 'suspend' ? '3' : $sid;
        $success = $this->updateStatus($mid, $status);

        if($success) {
            $this->createSuccessMessage($status);
        } else {
            $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    private function updateStatus($mid, $status)
    {
        try {
            $recurringRecord = $this->recurringRepository->getById(
                $mid,
                RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
            );
            $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurringRecord, $status);
            if($result) {
                $recurringRecord->setRecStatus((int)$status);
                $this->recurringRepository->save($recurringRecord);
                return true;
            }
        } catch (\Exception $e) {
            $this->tranApi->ebizLog()->crit($e->getMessage());
        }
        return false;
    }

    /**
     * Creates a success message
     *
     * @return ResponseInterface
     */
    private function createSuccessMessage($status)
    {
        $str = $status == 0 ? "Resubscribed" : "Unsubscribed";
        $str = $status == 3 ? "Suspended" : $str;

        $this->messageManager->addSuccessMessage(__("Subscription successfully $str."));
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]);
    }
}
