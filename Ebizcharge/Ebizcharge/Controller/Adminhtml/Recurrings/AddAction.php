<?php

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Ebizcharge\Ebizcharge\Controller\Adminhtml\AbstractAction;

/**
 * Add subscription
 *
 * Class AddAction
 */
class AddAction extends AbstractAction implements HttpGetActionInterface
{
    /**
     * Load the page defined in view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_addaction.xml
     *
     * @return Page
     */
    public function execute(): Page
    {
        return $this->_init($this->resultPageFactory->create());
    }
}
