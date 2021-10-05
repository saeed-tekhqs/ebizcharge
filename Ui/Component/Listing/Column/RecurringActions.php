<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring grid actions column
 *
 * Class RecurringActions
 */
class RecurringActions extends Column
{

    /**
     * Url path
     */
    const URL_PATH_EDIT = 'ebizcharge_ebizcharge/recurrings/editaction';

    const URL_PATH_EXPORT = 'ebizcharge_ebizcharge/recurrings/datesexportaction';
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['rec_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'mid' => $item['eb_rec_scheduled_payment_internal_id'],
                                    'magcid' => $item['mage_cust_id']
                                ]
                            ),
                            'label' => __('View')
                        ],
                        'export' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EXPORT,
                                [
                                    'mid' => $item['eb_rec_scheduled_payment_internal_id'],
                                    'magcid' => $item['mage_cust_id']
                                ]
                            ),
                            'label' => __('Export')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

}
