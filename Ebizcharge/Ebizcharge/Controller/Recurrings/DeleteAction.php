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

class DeleteAction implements AccountInterface
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
     * @param RecurringRepository $recurringRepository
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param Validator $fkValidator
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
     * Deletes customer's payment method.
     *
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

        $internalId = $this->request->getParam('internal_id');

        if (!empty($internalId)) {

            $deleteItems = explode(',', $internalId);
        }

        try {

            if (!empty($deleteItems)) {

                foreach ($deleteItems as $id) {

                    $params = array(
                        'securityToken' => $this->tranApi->getUeSecurityToken(),
                        'scheduledPaymentInternalId' => $id,
                        'statusId' => 3,
                    );

                    $res = $this->tranApi->getClient()->ModifyScheduledRecurringPaymentStatus($params);

                    $modifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;
                    if (!empty($modifyScheduledRecurringPaymentStatusResult)) {
                        if ($modifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                            try {
                                $recurring = $this->recurringRepository->getById($id,
                                    'eb_rec_scheduled_payment_internal_id')->setRecStatus(3);
                                $this->recurringRepository->save($recurring);
                            } catch (\Exception $e) {
                                return $this->createErrorResponse(self::ACTION_EXCEPTION);
                            }
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param $errorCode
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
     * @return Redirect
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(__('Subscription(s) successfully Deleted.'));
        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/listaction');
    }
}
