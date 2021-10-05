<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/30/21
 * Time: 4:43 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for order subscription search results.
 * @api
 * @since 100.0.2
 */
interface OrderSubscriptionSearchInterface extends SearchResultsInterface
{
    /**
     * Get order subscription list.
     * @return OrderSubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set order subscription list.
     * @param OrderSubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
