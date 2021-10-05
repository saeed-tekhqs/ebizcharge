<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 8:34 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Ebizcharge\Ebizcharge\Ui\ConfigProvider;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;

/**
 * Ebizcharge Payment Gateway Config
 *
 * Class Config
 */
class Config extends GatewayConfig
{
    const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = ConfigProvider::CODE,
        $pathPattern = GatewayConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

}
