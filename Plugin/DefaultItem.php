<?php

namespace Ebizcharge\Ebizcharge\Plugin;

use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    protected $tranApi;
    protected $config;

    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $quoteItemOptionCollection,
        \Ebizcharge\Ebizcharge\Model\TranApi $tranApi,
        \Ebizcharge\Ebizcharge\Model\Config $config
    )
    {
        $this->_tran = $tranApi;
        $this->config = $config;
        $this->quoteItemCollectionFactory = $quoteItemOptionCollection;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $productData = $this->getItemSubscriptionDetails($item);
        return array_merge($data, $productData);
    }

    /**
     * Get subscription data from quotation
     *
     * @return array
     */
    public function getSubscribedQuoteItemsDatadb($quote_custom_options_id, $quote_product_id)
    {
        $quoteItemCollection = $this->quoteItemCollectionFactory->create();
        $quoteItem = $quoteItemCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('item_id', $quote_custom_options_id)
            ->addFieldToFilter('product_id', $quote_product_id)
            ->getData();
        return $quoteItem;
    }

    /**
     * @param $item
     * @return array
     */
    public function getItemSubscriptionDetails($item)
    {
        try {
            $_item = $item;
            $quote_custom_options_id = $_item->getItemId();
            $quote_product_id = $_item->getProductId();

            $recurringDataDb = $this->getSubscribedQuoteItemsDatadb($quote_custom_options_id, $quote_product_id);
            $recurringDataJson = $recurringDataDb[0]['value'];
            $jasonDecodeRecurringData = json_decode($recurringDataJson, true);
            $jasonDecodeRecurringQty = $jasonDecodeRecurringData['qty'];
            $jasonDecodeRecurringDataArray = $jasonDecodeRecurringData['recurring'];

            if ((!empty($jasonDecodeRecurringDataArray)) && ($jasonDecodeRecurringDataArray['rec_activate'] == 1)) {
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
                $atts = array("product_subscribed" => '');
            }

        } catch (\Exception $ex) {
            $atts = array("product_subscribed" => '');
        }

        return $atts;
    }
}
