<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/9/21
 * Time: 11:53 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Abstract customer controller
 *
 * Class AbstractCustomerController
 */
abstract class AbstractCustomerController implements AccountInterface
{
    /**
     * @var null child class must define this variable
     */
    static $pageTitle = null;

    /**
     * @var PageFactory $pageFactory
     */
    protected $pageFactory;

    /**
     * @param PageFactory $pageFactory
     * @throws \Exception
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->getPageTitle();
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return Page|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__(static::$pageTitle));
        return $resultPage;
    }

    /**
     * This function force the child class to define static $pageTitle for the page
     *
     * @return string $pageTitle
     * @throws \Exception
     */
    protected function getPageTitle(): ?string
    {
        if (static::$pageTitle == null) {
            throw new \Exception('Child class ' . get_called_class() . ' failed to define static ' . static::$pageTitle . ' property');
        }
        return static::$pageTitle;
    }
}
