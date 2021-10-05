<?php
/**
 * Accesses data to pass to the 'Manage My Payment Method' pages.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Ebizcharge\Ebizcharge\Model\Config;

class Navigation extends \Magento\Customer\Block\Account\SortLink
{
    protected $config;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Config $config
    )
    {
        parent::__construct($context, $defaultPath);
        $this->_defaultPath = $defaultPath;
        $this->config = $config;
    }

    protected function _toHtml()
    {
        if ($this->config->isEbizchargeActive() == 1 && $this->config->isRecurringActive() == 1) {
            return parent::_toHtml();
        }

        return '';
    }
}
