<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ebizcharge\Ebizcharge\Block\Info;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;

/**
 * Credit card generic payment info
 *
 * @api
 * @since 100.0.2
 */
class Cc extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Prepare credit card related payment info
     *
     * @param \Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if(null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];

        if($this->getCcTypeName() == 'ACH') {
            if($ccType = $this->getCcTypeName()) {
                $data[(string)__('Payment Type')] = $ccType;
            }

            if($this->getInfo()->getCcLast4()) {
                $data[(string)__('Account Number')] = sprintf('xxxx%s', substr($this->getInfo()->getCcLast4(), -4));
            }
        } else {
            if($ccType = $this->getCcTypeName()) {
                $data[(string)__('Credit Card Type')] = $ccType;
            }

            if($this->getInfo()->getCcLast4()) {
                $data[(string)__('Credit Card Number')] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4());
            }
        }

        if(!$this->getIsSecureMode()) {
            if($ccSsIssue = $this->getInfo()->getCcSsIssue()) {
                $data[(string)__('Switch/Solo/Maestro Issue Number')] = $ccSsIssue;
            }
            $year = $this->getInfo()->getCcSsStartYear();
            $month = $this->getInfo()->getCcSsStartMonth();
            if($year && $month) {
                $data[(string)__('Switch/Solo/Maestro Start Date')] = $this->_formatCardDate($year, $month);
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
