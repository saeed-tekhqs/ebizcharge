<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/8/21
 * Time: 3:04 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Order cancel data builder class
 *
 * Class CancelDataBuilder
 * @package Ebizcharge\Ebizcharge\Gateway\Request
 */
class CancelDataBuilder implements BuilderInterface
{
    const ORDER_ID = 'orderId';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            self::ORDER_ID => $order->getOrderIncrementId()
        ];

    }

}
