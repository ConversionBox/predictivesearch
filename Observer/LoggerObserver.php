<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Observer;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

use Thecommerceshop\Predictivesearch\Model\ConfigData;

/**
 * Class for logging data
 */
class LoggerObserver implements ObserverInterface
{
    /**
     * @var PsrLoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * Logger constructor.
     *
     * @param PsrLoggerInterface $logger
     * @param Filesystem $fileSystem
     * @param File $file
     * @param ConfigData $configData
     */
    public function __construct(
        PsrLoggerInterface $logger,
        Filesystem $fileSystem,
        File $file,
        ConfigData $configData
    ) {
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        $this->configData = $configData;
        $this->directory = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Logger Observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->configData->isLoggingEnabled() == 0) {
                $this->logger->info("Disabled Log--");
                $this->delete();
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete Log File
     *
     * @return bool
     * @throws LocalizedException
     */
    public function delete()
    {
        $fileName= "log/typesenseError.log";
        if ($this->file->isExists($this->directory->getAbsolutePath().$fileName)) {
            $this->file->deleteFile($this->directory->getAbsolutePath().$fileName);
        }
    }
}
