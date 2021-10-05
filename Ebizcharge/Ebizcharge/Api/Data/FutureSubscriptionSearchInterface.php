<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/23/21
 * Time: 9:53 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for future subscription search results.
 * @api
 * @since 100.0.2
 */
interface FutureSubscriptionSearchInterface extends SearchResultsInterface
{
    /**
     * Get future subscription list.
     * @return FutureSubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set future subscription list.
     * @param FutureSubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
