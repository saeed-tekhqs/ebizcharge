<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/14/21
 * Time: 08:33 PM
 */
namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory\Grid as PaymentHistory;
use Psr\Log\LoggerInterface;

/***
 * Subscriptions Payment History CSV export class
 *
 * Class ExportPaymentHistoryCsv
 */
class ExportPaymentHistoryCsv extends Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
        $this->logger = $logger;
    }
    /**
     * Export customers most ordered report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $this->_view->loadLayout();
        $fileName = 'PaymentHistory.csv';
        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->createBlock(PaymentHistory::class);
        try {
            return $this->_fileFactory->create( $fileName, $exportBlock->getCsvFile(), DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
