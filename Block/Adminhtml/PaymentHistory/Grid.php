<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/11/21
 * Time: 11:33 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Ebizcharge\Ebizcharge\Model\PaymentHistory\Collection as PaymentHistoryCollection;

/**
 * Subscriptions Payment History grid
 *
 * Class Grid
 */
class Grid extends Extended
{
    /**
     * @var PaymentHistoryCollection
     */
    private $collection;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @param PaymentHistoryCollection $collection
     * @param Context $context
     * @param Data $backendHelper
     * @param TranApi $tranApi
     * @param array $data
     */
    public function __construct(
        PaymentHistoryCollection $collection,
        Context $context,
        Data $backendHelper,
        TranApi $tranApi,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collection = $collection;
        $this->tranApi = $tranApi;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection(): Grid
    {
        if ($this->applyFilters()){
            $this->setCollection($this->collection);
           return parent::_prepareCollection();
        }
        $recurringPayments = $this->getAllSearchRecurringPayments();
        $collection = $this->collection->addDataToCollection($this->collection, $recurringPayments);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns(): Grid
    {
        try {
            $this->addColumn(
                'massActionField',
                [
                    'header' => __('massActionField'),
                    'type' => 'text',
                    'sortable' => false,
                    'is_system' => true,
                    'filter' => false,
                    'index' => 'massActionField',
                    'column_css_class'=>'no-display',
                    'header_css_class'=>'no-display'
                ]
            )
                ->addColumn(
                'customerId',
                [
                    'header' => __('Customer ID'),
                    'type' => 'id',
                    'sortable' => false,
                    'index' => 'customerId',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
            )
                ->addColumn(
                    'customerName',
                    [
                        'header' => __('Customer Name'),
                        'index' => 'customerName',
                        'filter' => false,
                        'sortable' => false,
                        'type' => 'text'
                    ]
                )
                ->addColumn(
                    'paymentDate',
                    [
                        'header' => __('Payment Date'),
                        'type'      => 'datetime',
                        'sortable' => false,
                        'renderer' => DateRenderer::class,
                        'index' => 'paymentDate',
                        'header_css_class' => 'col-date col-date-min-width',
                        'column_css_class' => 'col-date'
                    ]
                )
                ->addColumn(
                    'paymentAmount',
                    [
                        'header' => __('Amount'),
                        'type' => 'id',
                        'filter' => false,
                        'sortable' => false,
                        'index' => 'paymentAmount',
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id',
                    ]
                )
                ->addColumn(
                'cardInfo',
                [
                    'header' => __('Payment Method'),
                    'type' => 'text',
                    'sortable' => false,
                    'filter' => false,
                    'index' => 'cardInfo',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
                ->addColumn(
                    'refNum',
                    [
                        'header' => __('Ref'),
                        'type' => 'text',
                        'index' => 'refNum',
                        'filter' => false,
                        'sortable' => false,
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id',
                    ]
                )
                ->addColumn(
                    'resultStatus',
                    [
                        'header' => __('Result'),
                        'renderer' => ResultRenderer::class,
                        'type' => 'text',
                        'sortable' => false,
                        'filter' => false,
                        'index' => 'resultStatus',
                        'header_css_class' => 'col-id',
                        'column_css_class' => 'col-id',
                    ]
                );
            $this->addColumn(
                'actions',
                [
                    'header' => __('Actions'),
                    'type' => 'select',
                    'renderer' => SelectRenderer::class,
                    'resizeEnabled' => true,
                    'resizeDefaultWidth' => 10,
                    'is_system' => true,
                    'filter' => false,
                    'id' => 'refNum',
                    'sortable' => false,
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select',
                ]
            );
            $this->addExportType('*/*/exportpaymenthistorycsv', __('CSV'));
            $this->addExportType('*/*/exportpaymenthistoryexl', __('Excel XML'));
            $block = $this->getLayout()->getBlock('grid.bottom.links');
        } catch (\Exception $e) {
            return parent::_prepareColumns();
        }
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        return parent::_prepareColumns();
    }

    public function getAllSearchRecurringPayments($customerId = null, $paymentDate = false): array
    {
        $start = $this->getPageNumber() * $this->getPageSize();
        $limit = $this->getPageSize();
        return $this->tranApi->getSearchTransactions($customerId, $start, $limit, $paymentDate);
    }

    /**
     * @return bool
     */
    public function applyFilters(): bool
    {
        return !empty(parent::getParam(parent::getVarNameFilter())) && is_string(parent::getParam(parent::getVarNameFilter()));
    }

    protected function _setFilterValues($data)
    {
        $customerId = false;
        $paymentDate = false;
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset(
                    $data[$columnId]
                ) && (is_array(
                        $data[$columnId]
                    ) && !empty($data[$columnId]) || strlen(
                        $data[$columnId]
                    ) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                $this->_addColumnFilterToCollection($column);

                $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
                if ($field === 'customerId') $customerId = $data[$columnId];
                if ($field === 'paymentDate' && isset($data[$columnId]['from'])) $paymentDate = $data[$columnId]['from'];
            }
        }
        $recurringPayments = $this->getAllSearchRecurringPayments($customerId, $paymentDate);
        $collection = $this->collection->addDataToCollection($this->collection, $recurringPayments);
        $this->setCollection($collection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('massActionField');
        $this->getMassactionBlock()->setFormFieldName('massActionField');
        $this->getMassactionBlock()->setHideFormElement(true);
        $this->getMassactionBlock()->addItem(
            'email',
            [
                'label' => __('Email'),
                'url' => $this->getUrl('ebizcharge_ebizcharge/*/emailbulkaction'),
                'confirm' => __('Are you sure?'),
            ]
        );
        return $this;
    }

    public function getPageSize(): int
    {
        return (int)$this->getParam($this->getVarNameLimit(), $this->_defaultLimit);
    }

    public function getPageNumber(): int
    {
        return (int)$this->getParam($this->getVarNamePage(), $this->_defaultPage);
    }

}
