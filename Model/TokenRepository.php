<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/22/21
 * Time: 12:42 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\TokenInterface;
use Ebizcharge\Ebizcharge\Api\TokenRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\TokenFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Token\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Token as Resource;
use Ebizcharge\Ebizcharge\Api\Data\TokenSearchInterfaceFactory as SearchFactory;
use Psr\Log\LoggerInterface;

/**
 * Contains operations related to ebizcharge_token table
 *
 * Class TokenRepository
 */
class TokenRepository extends AbstractRepository implements TokenRepositoryInterface
{
    /**
     * @param ModelFactory $modelFactory
     * @param SearchFactory $searchFactory
     * @param CollectionFactory $collectionFactory
     * @param Resource $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModelFactory $modelFactory,
        SearchFactory $searchFactory,
        CollectionFactory $collectionFactory,
        Resource $resource,
        LoggerInterface $logger
    ) {
        $this->modelFactory = $modelFactory;
        $this->searchFactory = $searchFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save(TokenInterface $record): ?TokenInterface
    {
        return parent::saveRecord($record);
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId, $field = null): ?TokenInterface
    {
        return parent::getRecordById($entityId, $field);
    }
}
