<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Rizwan
 * Date: 6/16/21
 * Time: 2:07 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * Class TokenInterface
 * @api
 */
interface TokenInterface
{
    const TOKEN_ID = 'token_id';

    const MAGE_CUST_ID = 'mage_cust_id';

    const EBZC_CUST_ID = 'ebzc_cust_id';

    /**
     * @param int $token_id
     * @return $this
     */
    public function setTokenId(int $token_id): TokenInterface;

    /**
     * @return int
     */
    public function getTokenId(): int;

    /**
     * @param int $mage_cust_id
     * @return $this
     */
    public function setMageCustId(int $mage_cust_id): TokenInterface;

    /**
     * @return int
     */
    public function getMageCustId(): int;

    /**
     * @param int $ebzc_cust_id
     * @return $this
     */
    public function setEbzcCustId(int $ebzc_cust_id): TokenInterface;

    /**
     * @return int
     */
    public function getEbzcCustId(): int;


}
