<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\ConfigSaveInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Cache\TypeListInterface;

class ConfigSave implements ConfigSaveInterface
{
    protected $configWriter;
    protected $cacheTypeList;

    public function __construct(
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList
    ) {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
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
