<?php
namespace Conversionbox\Predictivesearch\Api;

interface ConfigSaveInterface
{
    /**
     * Save multiple config values
     *
     * @param mixed $data
     * @return string
     */
    public function saveConfig($data);
}
