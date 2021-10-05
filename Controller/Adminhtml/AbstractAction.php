<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/10/21
 * Time: 11:08 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Provide basic functionality for other controllers
 *
 * Class AbstractController
 */
abstract class AbstractAction extends Action
{
    /**
     * ACL resource for access management
     */
    const ACL_RESOURCE = 'Ebizcharge_Ebizcharge::rec';

    /**
     * active menu id
     */
    const MENU_ID = 'Ebizcharge_Ebizcharge::rec';

    /**
     * @var string
     */
    protected static $pageTitle = 'Manage Subscriptions';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * initliazing breadcrumbs
     * @param $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Manage Subscriptions'), __('Manage Subscriptions'));
        $resultPage->getConfig()->getTitle()->prepend(__(static::$pageTitle));
        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(static::ACL_RESOURCE);
    }

}
