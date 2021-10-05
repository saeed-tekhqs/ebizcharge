<?php

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;


class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    protected $tranApi;
    protected $config;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $quoteItemOptionCollection,
        \Ebizcharge\Ebizcharge\Model\TranApi $tranApi,
        \Ebizcharge\Ebizcharge\Model\Config $config
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->_tran = $tranApi;
        $this->config = $config;
        $this->quoteItemCollectionFactory = $quoteItemOptionCollection;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $items = $result['totalsData']['items'];

        foreach ($items as $index => $item) {

            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $atts = $this->getItemSubscriptionDetails($quoteItem, $index);
            $result['quoteItemData'][$index]['subscribed'] = $atts['product_subscribed'];
            $result['quoteItemData'][$index]['frequency'] = $atts['product_frequency'];
            $result['quoteItemData'][$index]['qty_subscribed'] = $atts['product_qty_subscribed'];
            $result['quoteItemData'][$index]['sdate'] = $atts['product_sdate'];
            $result['quoteItemData'][$index]['edate'] = $atts['product_edate'];
        }
        return $result;
    }

    /**
     * @param $quote_custom_options_id
     * @param $quote_product_id
     * @return mixed
     */
    public function getSubscribedQuoteItemsDatadb($quote_custom_options_id, $quote_product_id)
    {

        $quoteItemCollection = $this->quoteItemCollectionFactory->create();
        $quoteItem           = $quoteItemCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('item_id', $quote_custom_options_id)
            ->addFieldToFilter('product_id', $quote_product_id)
            ->getData();
        return $quoteItem;
    }

    /**
     * Show subscription in order history page
     *
     * @return array
     */
    public function getItemSubscriptionDetails($item, $index)
    {
        try {
            $quote_id = $item->getQuoteId();
            $quote_custom_options_id = $item->getItemId();
            $quote_product_id = $item->getProductId();
            $quote_qty = $item->getQty();

            $recurringDataDb = $this->getSubscribedQuoteItemsDatadb($quote_custom_options_id, $quote_product_id);
            $recurringDataJson = $recurringDataDb[0]['value'];
            $jasonDecodeRecurringData = json_decode($recurringDataJson, true);
            $this->_tran->cronlog($jasonDecodeRecurringData);
            $jasonDecodeRecurringQty = $jasonDecodeRecurringData['qty'];
            $jasonDecodeRecurringDataArray = $jasonDecodeRecurringData['recurring'];

            if ((!empty($jasonDecodeRecurringDataArray)) && ($jasonDecodeRecurringDataArray['rec_activate'] == 1)) {
                $rec_activate = $jasonDecodeRecurringDataArray['rec_activate'];
                $rec_frequency = $jasonDecodeRecurringDataArray['rec_frequency'];
                $sdate = $jasonDecodeRecurringDataArray['sdate'];

                if (!empty($jasonDecodeRecurringDataArray['edate'])) {
                    $edate = $jasonDecodeRecurringDataArray['edate'];
                } else {
                    $edate = 'Recur indefinitely';
                }

                $atts = array(
                    "product_subscribed" => 'Subscribed to product(s)',
                    "product_frequency" => $rec_frequency,
                    "product_qty_subscribed" => $jasonDecodeRecurringQty,
                    "product_sdate" => $sdate,
                    "product_edate" => $edate
                );

            } else {

                $atts = array(
                    "product_subscribed" => 'One time purchase',
                    "product_frequency" => '',
                    "product_qty_subscribed" => '',
                    "product_sdate" => '',
                    "product_edate" => ''
                );
            }

        } catch (\Exception $ex) {
            $atts = array(
                "product_subscribed" => '',
                "product_frequency" => '',
                "product_qty_subscribed" => '',
                "product_sdate" => '',
                "product_edate" => ''
            );
        }
        return $atts;
    }
}
