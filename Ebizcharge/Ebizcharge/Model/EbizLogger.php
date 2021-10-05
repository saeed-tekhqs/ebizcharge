<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 8/27/21
 * Time: 12:26 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Laminas\Log\Writer\Stream as LogWriter;
use Laminas\Log\Logger as CustomLogger;
/**
 *  Custom LOG files
 */
const EBIZ_CCNNECT_LOG_FILE = '/var/log/econnect.log';
const EBIZ_CRON_LOG_FILE = '/var/log/ebizcron.log';


/**
 * This trait is for custom logging messages
 */
trait EbizLogger
{

    /**
     * Create custom logger for logging messages
     *
     * @param string $filePath
     * @return CustomLogger
     */
    private function createCustomLogFile(string $filePath): CustomLogger
    {
        $writer = new LogWriter(BP . $filePath);
        $logger = new CustomLogger();
        $logger->addWriter($writer);
        return $logger;
    }

    /**
     * Logger for ebiz gateway
     *
     * @return CustomLogger
     */
    public function ebizLog(): CustomLogger
    {
        return $this->createCustomLogFile(EBIZ_CCNNECT_LOG_FILE);

    }

    /**
     * Cron logger
     *
     * @return CustomLogger
     */
    public function ebizCronLog(): CustomLogger
    {
        return $this->createCustomLogFile(EBIZ_CRON_LOG_FILE);
    }


}
