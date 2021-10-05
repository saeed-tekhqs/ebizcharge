<?php
namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Ebizcharge\Ebizcharge\Controller\Adminhtml\AbstractAction;


/**
 * Class Index
 */
class Index extends AbstractAction implements HttpGetActionInterface
{
    /**
     * Load the page defined in view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_index.xml
     * @return Page
     */
    public function execute(): Page
    {
       return $this->_init($this->resultPageFactory->create());
    }
}
