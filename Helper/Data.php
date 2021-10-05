<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/10/21
 * Time: 3:05 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;

/**
 * Helper class to get configuration values
 *
 * Class Data
 */
class Data extends AbstractHelper
{

    /**
     * Config xml path constants
     */
    const XML_PATH_REQUEST_CARD_CODE_ADMIN = 'payment/ebizcharge_ebizcharge/request_card_code_admin';

    const XML_PATH_CCTYPES = 'payment/ebizcharge_ebizcharge/cctypes';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var State
     */
    private $appState;

    /**
     * Data constructor.
     * @param State $appState
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        State $appState,
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->appState = $appState;
    }

    /**
     * Get config value by path and scope etc
     *
     * @param $scopePath
     * @param string $scopeType
     * @param int|null $scopeCode
     * @return mixed
     */
    private function getConfigValue($scopePath, int $scopeCode = null,   string $scopeType = 'website')
    {
        return $this->scopeConfig->getValue($scopePath, $scopeType, $scopeCode);
    }

    /**
     * @return bool
     */
    private function isAdmin(): bool
    {
        try {
            return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * Get Request CardCode Admin
     *
     * @param int|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getRequestCardCodeAdmin(int $scopeCode = null,   string $scopeType = 'website')
    {
        return $this->getConfigValue(self::XML_PATH_REQUEST_CARD_CODE_ADMIN, $scopeCode, $scopeType);
    }

    /**
     * GET PAYMENT CCTYPES
     *
     * @param int|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getPaymentCctypes(int $scopeCode = null,   string $scopeType = 'website')
    {
        return $this->getConfigValue(self::XML_PATH_CCTYPES, $scopeCode, $scopeType);

    }

}
