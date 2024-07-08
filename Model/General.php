<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model;

use Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Url\EncoderInterface;
use Thecommerceshop\Predictivesearch\Logger\Logger;

class General
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepositoryInterface;

    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var EncoderInterface
     */
    private $encoderInterface;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * General Constructor
     *
     * @param StoreManagerInterface $storeManagerInterface
     * @param Json $json
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param StockItemRepository $stockItemRepository
     * @param EncoderInterface $encoderInterface
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        Json $json,
        ProductRepositoryInterface $productRepositoryInterface,
        StockItemRepository $stockItemRepository,
        EncoderInterface $encoderInterface,
        Logger $logger
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->json = $json;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->stockItemRepository =  $stockItemRepository;
        $this->encoderInterface = $encoderInterface;
        $this->logger = $logger;
    }

    /**
     * Get Media Url
     */
    public function getMediaUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get Store
     *
     * @param int $storeId
     */
    public function getStore($storeId = null)
    {
        return $this->storeManagerInterface->getStore($storeId);
    }

    /**
     * Get All Store
     */
    public function getAllStore()
    {
        return $this->storeManagerInterface->getStores();
    }

    /**
     * Encode data
     *
     * @param array $data
     * @return string
     */
    public function encodeData($data)
    {
        try {
            if ($data) {
                return $this->json->serialize($data);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Decode data
     *
     * @param string $data
     * @return array
     */
    public function decodeData($data)
    {
        try {
            if ($data) {
                return $this->json->unserialize($data);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get product Data by ID
     *
     * @param int $productId
     * @param int $storeId
     */
    public function getProductData($productId, $storeId = null)
    {
        try {
            return $this->productRepositoryInterface->getById($productId, false, $storeId);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Stock Info
     *
     * @param int $productId
     */
    public function getStockInfo($productId)
    {
        try {
            return $this->stockItemRepository->get($productId);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Base64 Encode
     *
     * @param string $data
     * @return string
     */
    public function base64Encode($data)
    {
        try {
            if ($data) {
                return $this->encoderInterface->encode($data);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
