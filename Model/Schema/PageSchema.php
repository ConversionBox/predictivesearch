<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Schema;

use Thecommerceshop\Predictivesearch\Model\Types\TypesenseTypes;

class PageSchema
{
    /**
     * Return product shcema structure
     *
     * @param string $indexName
     * @return array
     */
    public function getPageSchema($indexName)
    {
        return [
            'name'   => $indexName,
            'fields' => [
            ['name' => 'id', 'type' => TypesenseTypes::STRING],
            ['name' => 'page_id', 'type' => TypesenseTypes::STRING],
            ['name' => 'page_title', 'type' => TypesenseTypes::STRING],
            ['name' => 'url', 'type' => TypesenseTypes::STRING],
            ['name' => 'identifier', 'type' => TypesenseTypes::STRING],
            ['name' => 'status', 'type' => TypesenseTypes::INTEGER],
            ['name' => 'created_at', 'type' => TypesenseTypes::STRING],
            ['name' => 'store', 'type' => TypesenseTypes::STRING, 'facet' => true]
            ],
        ];
    }
}
