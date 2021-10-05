<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Cards delete action
 *
 * Class DeleteAction
 */
class DeleteAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @param ManagerInterface $messageManager
     * @param RecurringRepositoryInterface $recurringRepository
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TranApi $tranApi
     * @param Validator $fkValidator
     */
    public function __construct(
        ManagerInterface $messageManager,
        RecurringRepositoryInterface $recurringRepository,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TranApi $tranApi,
        Validator $fkValidator
    ) {
        $this->messageManager = $messageManager;
        $this->recurringRepository = $recurringRepository;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
    public function execute(): Redirect
    {
        if (!$this->request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($this->request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');
        $cust_id = $this->request->getParam('cust_id');

        if ($cid === null || $mid === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(
                    RecurringInterface::MAGE_CUST_ID, $cust_id
                )
                ->addFilter(
                    RecurringInterface::EB_REC_METHOD_ID,
                    $mid
                )
                ->addFilter(
                    RecurringInterface::REC_STATUS,
                    0
                );
            $result = $this->recurringRepository->getList($searchCriteria->create());
            $result = current($result->getItems());

            if (is_array($result) && count($result) > 0) {
                $scheduledPaymentInternalId = $result->getData('eb_rec_scheduled_payment_internal_id');
                $params = array(
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                    'statusId' => 1,
                );

                $res = $this->tranApi->getClient()->ModifyScheduledRecurringPaymentStatus($params);

                $modifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;

                if ($modifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                    try {
                        $recordToUpdate = $this->recurringRepository->getById($scheduledPaymentInternalId,
                            'eb_rec_scheduled_payment_internal_id')
                            ->setRecStatus(1);
                        $this->recurringRepository->save($recordToUpdate);
                    } catch (\Exception $e) {
                        $this->tranApi->ebizLog()->info($e->getMessage());
                    }
                }
            }

            $params = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerToken' => $cid,
                'paymentMethodId' => $mid,
            );

            $this->tranApi->getClient()->deleteCustomerPaymentMethodProfile($params);

        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param $errorCode
     * @return Redirect
     */
    private function createErrorResponse($errorCode): Redirect
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]);
        return $this->redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return Redirect
     */
    private function createSuccessMessage(): Redirect
    {
        $this->messageManager->addSuccessMessage(__('Credit card was successfully removed.'));
        return $this->redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }
}
