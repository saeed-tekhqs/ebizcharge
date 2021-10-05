<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Model\Order\Item;

/**
 * Adminhtml sales order item renderer
 *
 * @api
 * @since 100.0.2
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer
{
    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * Checkout helper
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     */
    protected $_giftMessage = [];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Ebizcharge\Ebizcharge\Model\TranApi $tranApi,
        \Ebizcharge\Ebizcharge\Model\Config $config,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    )
    {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_messageHelper = $messageHelper;
        $this->_tran = $tranApi;
        $this->config = $config;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Get order item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_getData('item');
    }

    /**
     * Retrieve real html id for field
     *
     * @param string $id
     * @return string
     */
    public function getFieldId($id)
    {
        return $this->getFieldIdPrefix() . $id;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     */
    public function getFieldIdPrefix()
    {
        return 'order_item_' . $this->getItem()->getId() . '_';
    }

    /**
     * Indicate that block can display container
     *
     * @return bool
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function canDisplayContainer()
    {
        return $this->getRequest()->getParam('reload') != 1;
    }

    /**
     * Retrieve default value for giftmessage sender
     *
     * @return string
     */
    public function getDefaultSender()
    {
        if (!$this->getItem()) {
            return '';
        }

        if ($this->getItem()->getOrder()) {
            return $this->getItem()->getOrder()->getBillingAddress()->getName();
        }

        return $this->getItem()->getBillingAddress()->getName();
    }

    /**
     * Retrieve default value for giftmessage recipient
     *
     * @return string
     */
    public function getDefaultRecipient()
    {
        if (!$this->getItem()) {
            return '';
        }

        if ($this->getItem()->getOrder()) {
            if ($this->getItem()->getOrder()->getShippingAddress()) {
                return $this->getItem()->getOrder()->getShippingAddress()->getName();
            } elseif ($this->getItem()->getOrder()->getBillingAddress()) {
                return $this->getItem()->getOrder()->getBillingAddress()->getName();
            }
        }

        if ($this->getItem()->getShippingAddress()) {
            return $this->getItem()->getShippingAddress()->getName();
        } elseif ($this->getItem()->getBillingAddress()) {
            return $this->getItem()->getBillingAddress()->getName();
        }

        return '';
    }

    /**
     * Retrieve real name for field
     *
     * @param string $name
     * @return string
     */
    public function getFieldName($name)
    {
        return 'giftmessage[' . $this->getItem()->getId() . '][' . $name . ']';
    }

    /**
     * Initialize gift message for entity
     *
     * @return $this
     */
    protected function _initMessage()
    {
        $this->_giftMessage[$this->getItem()->getGiftMessageId()] = $this->_messageHelper->getGiftMessage(
            $this->getItem()->getGiftMessageId()
        );

        // init default values for giftmessage form
        if (!$this->getMessage()->getSender()) {
            $this->getMessage()->setSender($this->getDefaultSender());
        }
        if (!$this->getMessage()->getRecipient()) {
            $this->getMessage()->setRecipient($this->getDefaultRecipient());
        }

        return $this;
    }

    /**
     * Retrieve gift message for entity
     *
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getMessage()
    {
        if (!isset($this->_giftMessage[$this->getItem()->getGiftMessageId()])) {
            $this->_initMessage();
        }

        return $this->_giftMessage[$this->getItem()->getGiftMessageId()];
    }

    /**
     * Retrieve save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'sales/order_view_giftmessage/save',
            ['entity' => $this->getItem()->getId(), 'type' => 'order_item', 'reload' => true]
        );
    }

    /**
     * Retrieve block html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return substr($this->getFieldIdPrefix(), 0, -1);
    }

    /**
     * Indicates that block can display giftmessages form
     *
     * @return bool
     */
    public function canDisplayGiftmessage()
    {
        return $this->_messageHelper->isMessagesAllowed(
            'order_item',
            $this->getItem(),
            $this->getItem()->getOrder()->getStoreId()
        );
    }

    /**
     * Display susbtotal price including tax
     *
     * @param Item $item
     * @return string
     */
    public function displaySubtotalInclTax($item)
    {
        return $this->displayPrices(
            $this->_checkoutHelper->getBaseSubtotalInclTax($item),
            $this->_checkoutHelper->getSubtotalInclTax($item)
        );
    }

    /**
     * Display item price including tax
     *
     * @param Item|\Magento\Framework\DataObject $item
     * @return string
     */
    public function displayPriceInclTax(\Magento\Framework\DataObject $item)
    {
        return $this->displayPrices(
            $this->_checkoutHelper->getBasePriceInclTax($item),
            $this->_checkoutHelper->getPriceInclTax($item)
        );
    }

    /**
     * Retrieve rendered column html content
     *
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param string $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 100.1.0
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                break;
            case 'status':
                $html = $item->getStatus();
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('original_price');
                break;
            case 'subscribed':
                $html = $this->getItemSubscription();
                break;
            case 'tax-amount':
                $html = $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-percent':
                $html = $this->displayTaxPercent($item);
                break;
            case 'discont':
                $html = $this->displayPriceAttribute('discount_amount');
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    /**
     * Get columns data.
     *
     * @return array
     * @since 100.1.0
     */
    public function getColumns()
    {
        $columns = array_key_exists('columns', $this->_data) ? $this->_data['columns'] : [];
        return $columns;
    }

    // ----- for order recurring data display ----- //

    public function getSubscribedItemsData($orderId, $orderMainItemId, $infoBuyRequestParentItemId, $infoBuyRequestChildItemId)
    {
        if (!empty($infoBuyRequestParentItemId)) {
            $orderMainItemId = $infoBuyRequestParentItemId;
        }

        if (!empty($infoBuyRequestChildItemId)) {
            if ($infoBuyRequestChildItemId == $orderMainItemId) {
                $infoBuyRequestChildItemId = $orderMainItemId;
            }
        } else {
            $infoBuyRequestChildItemId = $orderMainItemId;
        }

        $recurring = [];
        $selectQueryParameters = array(
            'option' => 'select',
            'tableName' => 'ebizcharge_recurring',
            'tableFields' => '*',
            'whereKey' => 'mage_order_id',
            'whereValue' => $orderId,
            'whereKey2' => 'mage_parent_item_id',
            'whereValue2' => $orderMainItemId,
            'whereKey3' => 'mage_item_id',
            'whereValue3' => $infoBuyRequestChildItemId
        );

        $recurringData = $this->_tran->runSelectQueryMultipleInput($selectQueryParameters, 3);

        if (isset($recurringData[0]) && !empty($recurringData[0])) {
            $recurring = $recurringData[0];

        } else {
            // else check in recurring_order
            $selectQueryParameters = array(
                'option' => 'select',
                'tableName' => 'ebizcharge_recurring_order',
                'tableFields' => '*',
                'whereKey' => 'rec_order_id',
                'whereValue' => $orderId
            );

            $recurringOrder = $this->_tran->runSelectQuery($selectQueryParameters);

            if (isset($recurringOrder[0]) && !empty($recurringOrder[0])) {
                $recurringOrder = $recurringOrder[0];
                $selectQueryParameters = array(
                    'option' => 'select',
                    'tableName' => 'ebizcharge_recurring',
                    'tableFields' => '*',
                    'whereKey' => 'rec_id',
                    'whereValue' => $recurringOrder['recurring_id']
                );

                $recurringData = $this->_tran->runSelectQuery($selectQueryParameters);

                if (isset($recurringData[0]) && !empty($recurringData[0])) {
                    $recurring = $recurringData[0];
                }
            }
        }

        return $recurring;
    }

    /**
     * Show subscription in order history page
     *
     * @return string
     */
    public function getItemSubscription()
    {
        try {
            $order = $this->getItem()->getOrder();
            $rawData = $this->getItem()->getData();
            $orderItemId = $rawData['product_id'];

            if (!empty($rawData['product_options']['info_buyRequest'])) {
                $infoBuyRequest = $rawData['product_options']['info_buyRequest'];
                $infoBuyRequestParentItemId = !empty($infoBuyRequest['item']) ? $infoBuyRequest['item'] : '';
                $infoBuyRequestChildItemId = !empty($infoBuyRequest['currentpid']) ? $infoBuyRequest['currentpid'] : '';
            } else {
                $infoBuyRequestParentItemId = '';
                $infoBuyRequestChildItemId = '';
            }

            $recurring = $this->getSubscribedItemsData($order->getIncrementId(), $orderItemId, $infoBuyRequestParentItemId, $infoBuyRequestChildItemId);

            if (!empty($recurring)) {
                $sdate = date('Y-m-d', strtotime($recurring['eb_rec_start_date']));

                if ($recurring['rec_indefinitely'] == 1) {
                    $edate = 'Recur indefinitely';
                } else {
                    $edate = date('Y-m-d', strtotime($recurring['eb_rec_end_date']));
                }

                $finalString = '<div class="ordersub">Status: <strong>Subscribed</strong></div>';
                $finalString .= '<div class="ordersub">Frequency: <strong>' . ucfirst($recurring['eb_rec_frequency']) . '</strong></div>';
                $finalString .= '<div class="ordersub">Qty Subscribed: <strong>' . $recurring['qty_ordered'] . '</strong></div>';
                $finalString .= '<div class="ordersub">Start Date: <strong>' . $sdate . '</strong></div>';
                $finalString .= '<div class="ordersub">End Date: <strong>' . $edate . '</strong></div>';

            } else {
                $finalString = '<div class="ordersub">Not subscribed</div>';
            }

            return $finalString;

        } catch (\Exception $ex) {
            $finalString = '<div class="ordersub">No record found!</div>';
            return $finalString;
        }
    }

}
