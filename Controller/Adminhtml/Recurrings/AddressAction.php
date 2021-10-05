<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Psr\Log\LoggerInterface;


/**
 * Save address of customer
 *
 * Class AddressAction
 */
class AddressAction extends Action implements HttpPostActionInterface
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @param RegionInterfaceFactory $regionFactory
     * @param LoggerInterface $logger
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Context $context
     */
    public function __construct(
        RegionInterfaceFactory $regionFactory,
        LoggerInterface $logger,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        Context $context
    )
    {
        parent::__construct($context);
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->regionFactory = $regionFactory;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue('customerIdAddress') && $this->saveAddress()) {
            echo '1';
        } else {
            echo '0';
        }
    }

    /**
     * This function save address
     *
     * @return bool
     */
    public function saveAddress(): bool
    {
        try {
            $address = $this->addressFactory->create();
            $post = $this->getRequest()->getPostValue();
            $street = join(' ', array_filter(array($post['ship_address1'] ?? '', $post['ship_address2'] ?? '', $post['ship_address3'] ?? '')));

            $address->setFirstname($post['ship_first_name'] ?? '')
                ->setLastname($post['ship_last_name'] ?? '')
                ->setCompany($post['ship_company'] ?? '')
                ->setCustomerId($post['customerIdAddress'] ?? '')
                ->setTelephone($post['ship_phone'] ?? '')
                ->setData('street', $street)
                ->setCity($post['ship_city'] ?? '')
                ->setRegionId($post['ship_region'] ?? '')
                ->setRegion($this->regionFactory->create()->setRegionId((int)$post['ship_region'] ?? ''))
                ->setPostcode($post['ship_zipcode'] ?? '')
                ->setCountryId($post['ship_country'] ?? '');
            $this->addressRepository->save($address);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
}
