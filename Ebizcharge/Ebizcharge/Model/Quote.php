<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/21/21
 * Time: 8:50 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Psr\Log\LoggerInterface;

/**
 * Get current quote and item class
 *
 * Class Quote
 */
class Quote
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BackendQuote
     */
    private $backendQuoteSession;

    /**
     * @var State
     */
    private $appState;

    /**
     * Quote constructor.
     * @param CheckoutSession $checkoutSession
     * @param BackendQuote $backendQuoteSession
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        BackendQuote $backendQuoteSession,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->appState = $appState;
    }

    /**
     * Get current quote
     *
     * @return CartInterface|QuoteModel|null
     */
    public function getQuote()
    {
        try {
            if($this->isAdmin()) {
                return $this->backendQuoteSession->getQuote();
            }
            return $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return null;
    }

    /**
     * Check if admin side
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isAdmin(): bool
    {
        return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
    }

}
