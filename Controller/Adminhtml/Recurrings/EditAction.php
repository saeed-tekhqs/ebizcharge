<?php
namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Ebizcharge\Ebizcharge\Controller\Adminhtml\AbstractAction;

/**
 * Class Index
 */
class EditAction extends AbstractAction implements HttpGetActionInterface
{
    /**
     * @var string
     */
    protected static $pageTitle = 'Manage Subscription Payment History';

    /**
     * Load the page defined in view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_editaction.xml
     *
     * @return Redirect|Page
     */
    public function execute()
    {
       $mid = $this->getRequest()->getParam('mid');

		if ($mid) {
            return $this->_init($this->resultPageFactory->create());
		} else {
            $this->messageManager->addErrorMessage(__('Unable to update recurrings payment.'));
		}
		return $this->resultRedirectFactory->create()->setPath('*/recurrings/');
	}
}
