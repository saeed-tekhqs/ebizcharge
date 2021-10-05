<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/22/21
 * Time: 12:45 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
/**
 * Interface for recurring search results.
 * @api
 * @since 100.0.2
 */
interface RecurringSearchResultInterface extends SearchResultsInterface
{

    /**
     * Get recurring list.
     * @return RecurringInterface[]
     */
    public function getItems();

    /**
     * Set recurring list.
     * @param RecurringInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
