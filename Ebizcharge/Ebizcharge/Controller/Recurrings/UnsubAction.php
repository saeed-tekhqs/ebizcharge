<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

class UnsubAction implements AccountInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param Validator $fkValidator
     * @param RecurringRepository $recurringRepository
     * @param TranApi $tranApi
     */
    public function __construct(
        ManagerInterface $messageManager,
        RecurringRepository $recurringRepository,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi,
        Validator $fkValidator
    ) {
        $this->messageManager = $messageManager;
        $this->recurringRepository = $recurringRepository;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->tranApi = $tranApi;
        $this->fkValidator = $fkValidator;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * @return Redirect
     */
    public function execute()
    {

        if (!$this->request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($this->request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $mid = $this->request->getParam('mid');
        $sid = $this->request->getParam('sid');
        try {
            $ueSecurityToken = $this->tranApi->getUeSecurityToken();
            $client = $this->tranApi->getClient();

            //0 = Active, 1 = Suspended, 2 = Expired, 3 = Canceled
            if (!empty($mid)) {
                $params = array(
                    'securityToken' => $ueSecurityToken,
                    'scheduledPaymentInternalId' => $mid,
                    'statusId' => $sid,
                );

                $res = $client->ModifyScheduledRecurringPaymentStatus($params);
                $ModifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;

                if (!empty($ModifyScheduledRecurringPaymentStatusResult)) {
                    if ($ModifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                        try {
                            $recurring = $this->recurringRepository->getById($mid,
                                'eb_rec_scheduled_payment_internal_id')->setRecStatus((int)$sid);
                            $this->recurringRepository->save($recurring);
                        } catch (\Exception $e) {
                            return $this->createErrorResponse(self::ACTION_EXCEPTION);
                        }

                    }
                }
            }

        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage($sid);
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return Redirect
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]);

        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/listaction');

    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param $id
     * @return Redirect
     */
    private function createSuccessMessage($id)
    {
        $str = $id == 0 ? 'Resubscribed' : 'Unsubscribed';

        $this->messageManager->addSuccessMessage(__("Subscription successfully $str."));

        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/listaction');
    }
}
