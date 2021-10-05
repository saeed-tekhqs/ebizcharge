<?php
declare(strict_types=1);
/**
 * Instantiates the model, and initializes the corresponding resource model.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\TokenInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Token as TokenResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Model class for `ebizcharge_token` table
 *
 * Class Token
 * @package Ebizcharge\Ebizcharge\Model
 */
class Token extends AbstractModel implements TokenInterface, IdentityInterface
{
    /**
     * cache identifier for token table
     */
    const CACHE_TAG = 'ebizcharge_token';

    /**
     * used for cache
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(TokenResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function setTokenId(int $token_id): TokenInterface
    {
        return $this->setData(self::TOKEN_ID, $token_id);
    }

    /**
     * @inheritDoc
     */
    public function getTokenId(): int
    {
        return (int)$this->getData(self::TOKEN_ID);

    }

    /**
     * @inheritDoc
     */
    public function setMageCustId(int $mage_cust_id): TokenInterface
    {
        return $this->setData(self::MAGE_CUST_ID, $mage_cust_id);
    }

    /**
     * @inheritDoc
     */
    public function getMageCustId(): int
    {
        return (int)$this->getData(self::MAGE_CUST_ID);

    }

    /**
     * @inheritDoc
     */
    public function setEbzcCustId(int $ebzc_cust_id): TokenInterface
    {
        return $this->setData(self::EBZC_CUST_ID, $ebzc_cust_id);
    }

    /**
     * @inheritDoc
     */
    public function getEbzcCustId(): int
    {
        return (int)$this->getData(self::EBZC_CUST_ID);
    }
}
