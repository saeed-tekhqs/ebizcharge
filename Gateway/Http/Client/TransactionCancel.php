<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/8/21
 * Time: 2:59 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Http\Client;

/**
 * Cancel command request
 *
 * Class TransactionCancel
 * @package Ebizcharge\Ebizcharge\Gateway\Http\Client
 */
class TransactionCancel extends AbstractTransaction
{
    /**
     * @inheritDoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return $this->adapterFactory->create($storeId)->cancel($data);
    }

}
