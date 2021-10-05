<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 2:05 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Http\Client;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class TransactionVoid
 * @package Ebizcharge\Ebizcharge\Gateway\Http\Client
 */
class TransactionVoid extends AbstractTransaction
{

    /**
     * @inheritDoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return $this->adapterFactory->create($storeId)->void($data);
    }
}
