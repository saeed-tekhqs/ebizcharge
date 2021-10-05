<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 9:09 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Validator;

use Ebizcharge\Ebizcharge\Gateway\SubjectReader;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

/**
 * Validate response
 *
 * Class ResponseValidator
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\ObjectManager\TMap
     */
    private $validators;

    /**
     * @var array
     */
    private $chainBreakingValidators;

    /**
     * ResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param TMapFactory $tmapFactory
     * @param SubjectReader $subjectReader
     * @param array $validators
     * @param array $chainBreakingValidators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMapFactory $tmapFactory,
        SubjectReader $subjectReader,
        array $validators = [],
        array $chainBreakingValidators = []
    ) {
        $this->subjectReader = $subjectReader;
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
        $this->chainBreakingValidators = $chainBreakingValidators;
        parent::__construct($resultFactory);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];
        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
