<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\ConfigSaveInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigSave implements ConfigSaveInterface
{
    protected $configWriter;
    protected $cacheTypeList;
    protected $storeManager;

    public function __construct(
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        StoreManagerInterface $storeManager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
         $this->storeManager = $storeManager;
    }

    /**
     * Save multiple config values
     *
     * @param mixed $data
     * @return string
     * @throws LocalizedException
     */
    public function saveConfig($data)
    {
            foreach ($data as $data) {
                $scope = 'default';
                $scopeId = 0;
           if (!empty($data['store_code'])) {
            $store = $this->storeManager->getStore($data['store_code']);
            $scope = 'stores';
            $scopeId = (int)$store->getId();
         } elseif (!empty($data['website_code'])) {
            $website = $this->storeManager->getWebsite($data['website_code']);
            $scope = 'websites';
            $scopeId = (int)$website->getId();
        }

                $path = $data['path'];
                $value = $data['value'];
                if($path === "typesense_search_result/instant_search_result/search_filters" || $path ==="typesense_search_result/instant_search_result/sort_options"){
                 $value = json_encode($data['value']); 
                 }else{
                   $value = $data['value'];
                }
                $this->configWriter->save($data['path'], $value, $scope, $scopeId);
            }
            // Clear config cache
            $this->cacheTypeList->cleanType('config');
            return "Configuration values saved successfully.";
       
    }
}
