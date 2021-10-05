<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/3/21
 * Time: 2:45 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Http\Client;

/**
 * Authorize Transaction command
 *
 * Class TransactionAuthorize
 * @package Ebizcharge\Ebizcharge\Gateway\Http\Client
 */
class TransactionAuthorize extends AbstractTransaction
{
    /**
     * Process authorize payment request
     * @param array $data
     * @return mixed
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        unset($data['store_id']);
        return $this->adapterFactory->create($storeId)->authorize($data);
    }

}
