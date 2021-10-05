<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/21/21
 * Time: 9:36 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add additional data in current quote
 *
 * Class AddAdditionalDataObserver
 */
class AddAdditionalDataObserver implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $input = $observer->getData('input');
        $data = $observer->getData('payment');
        $method = $input->getData('method');
        $additionalData = $input->getData('additional_data');
        $quote = $data->getQuote();
        if ($method == 'ebizcharge_ebizcharge') {
            $additionalData["method_title"] = $method;
            $quote->getPayment()->setAdditionalInformation(
                $additionalData
            );
            $quote->getPayment()->save();
        }
    }
}
