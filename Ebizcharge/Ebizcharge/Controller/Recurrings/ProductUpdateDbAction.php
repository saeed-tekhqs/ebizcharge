<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;

/**
 *
 * Class ProductUpdateDbAction
 */
class ProductUpdateDbAction implements AccountInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $itemOptionCollectionFactory;

    /**
     * @var Option
     */
    private $quoteOptionsModel;

    /**
     * @param CollectionFactory $itemOptionCollectionFactory
     * @param Option $quoteOptionsModel
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $itemOptionCollectionFactory,
        Option $quoteOptionsModel,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->quoteOptionsModel = $quoteOptionsModel;
    }

    /**
     * {"rec_activate":"1","rec_frequency":"daily","sdate":"2021-02-21","edate":"2021-02-22"},"currentpid":"","qty":"3"}
     * {"rec_activate":"0","rec_frequency":"","sdate":"","edate":""},"currentpid":"","qty":"1"}
     * @return bool
     */
    public function execute()
    {
        $active = $this->request->getParam('active') ?? 0;
        $frequency = $this->request->getParam('frequency');
        $sdate = $this->request->getParam('sdate');
        $edate = $this->request->getParam('edate');
        $qty = $this->request->getParam('qty') ?? 1;
        $cartItemID = $this->request->getParam('cartItemID');
        $cartProductID = $this->request->getParam('cartProductID');


        // get existing values
        $recurringData = $this->getSubscribedQuoteItemsDatadb($cartItemID, $cartProductID);
        $recurringDataJson = $recurringData->getData('value');

        //json decode
        $jasonDecodeRecurringData = json_decode($recurringDataJson, true);
        //$jasonDecodeRecurringQty = $jasonDecodeRecurringData['qty'];
        //$jasonDecodeRecurringDataArray = $jasonDecodeRecurringData['recurring'];

        // updating with new values
        $jasonDecodeRecurringData['qty'] = $qty;
        $jasonDecodeRecurringData['recurring']['rec_activate'] = $active;
        $jasonDecodeRecurringData['recurring']['rec_frequency'] = $frequency;
        $jasonDecodeRecurringData['recurring']['sdate'] = $sdate;
        $jasonDecodeRecurringData['recurring']['edate'] = $edate;
        //json encode
        $jasonDecodeRecurringDataEncode = json_encode($jasonDecodeRecurringData, true);

        return $this->saveQouteItemOptions($recurringData->getData('option_id'), $jasonDecodeRecurringDataEncode);

    }

    public function getSubscribedQuoteItemsDatadb($quote_custom_options_id, $quote_product_id)
    {
        $recurringDataEntries = $this->itemOptionCollectionFactory->create()
            ->addFilter('item_id', $quote_custom_options_id)
            ->addFilter('product_id', $quote_product_id);
        return $recurringDataEntries->getFirstItem();
    }

    /**
     * save quote item options to table
     *
     * @param $id
     * @param $data
     * @return bool
     */
    private function saveQouteItemOptions($id, $data)
    {
        try {
            $quoteOptions = $this->quoteOptionsModel->load($id);
            $quoteOptions->setData('value', $data);
            $quoteOptions->save();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}
