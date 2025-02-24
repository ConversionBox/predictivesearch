<?php
namespace Conversionbox\Predictivesearch\Api;

interface ConfigInterface
{
    /**
     * Get configuration by section
     *
     * @param string $section
     * @return array
     */
    public function getBySection($section);
}
