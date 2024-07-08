<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Error log name
     *
     * @var string
     */
    protected $fileName = '/var/log/typesenseError.log';
}
