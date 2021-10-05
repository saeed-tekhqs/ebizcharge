<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 11:59 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Adapter;

use Ebizcharge\Ebizcharge\Gateway\Config\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This factory is preferable to use for Ebizcharge adapter instance creation.
 *
 * Class EbizchargeAdapterFactory
 */
class EbizchargeAdapterFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }
    /**
     * Create credentials and place order
     * @param null $storeId
     * @return mixed
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            EbizchargeAdapter::class
        );
    }

}
