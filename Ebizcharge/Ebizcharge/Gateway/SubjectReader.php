<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 8:32 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway;

use Magento\Payment\Gateway\Helper;

/**
 * Subject reader readResponseObject
 *
 * Class SubjectReader
 */
class SubjectReader
{
    /**
     * Read Response Object
     * @param array $subject
     * @return mixed
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }
        return $response['object'];
    }

}
