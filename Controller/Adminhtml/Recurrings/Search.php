<?php
namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Ebizcharge\Ebizcharge\Controller\Adminhtml\AbstractAction;

/**
 * Class Search
 */
class Search extends AbstractAction implements HttpGetActionInterface
{
    /**
     * @var string
     */
    protected static $pageTitle = 'Search Future Subscriptions';

    /**
     * Load the page defined in view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_search.xml
     *
     * @return Page
     */
    public function execute(): Page
    {
        return $this->_init($this->resultPageFactory->create());
    }
}
