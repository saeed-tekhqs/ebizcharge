<?php
/**
* Resource model which collects multiple 'Token' models.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Token;

use Ebizcharge\Ebizcharge\Model\Token as TokenModel;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Token as TokenResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * ebizcharge token collection
 *
 * Class Collection
 * @package Ebizcharge\Ebizcharge\Model\ResourceModel\Token
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(TokenModel::class, TokenResource::class);
    }
}
