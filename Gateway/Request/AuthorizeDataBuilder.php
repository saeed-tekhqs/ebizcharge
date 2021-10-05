<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 9:05 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Ebizcharge\Ebizcharge\Model\Quote as QuoteModel;
use Magento\Payment\Helper\Formatter;

/**
 *
 * Class AuthorizeDataBuilder
 */
class AuthorizeDataBuilder implements BuilderInterface
{
    use Formatter;
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var QuoteModel
     */
    private $quoteModel;

    /**
     * AuthorizeDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param QuoteModel $quoteModel
     */
    public function __construct(
        SubjectReader $subjectReader,
        QuoteModel $quoteModel
    ) {
        $this->subjectReader = $subjectReader;
        $this->quoteModel = $quoteModel;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $quote = $this->quoteModel->getQuote();
        $amount = $quote->getGrandTotal();
        return [
            'amount' => $amount,
            'payment' => $paymentDO->getPayment()

        ];

    }
}
