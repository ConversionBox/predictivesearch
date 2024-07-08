<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Types;

class TypesenseTypes
{
    /**
     * String Values
     */
    public const STRING = 'string';

    /**
     * Array of strings
     */
    public const ARRAY_STRTING = 'string[]';

    /**
     * Integer values up to 2,147,483,647
     */
    public const INTEGER = 'int32';

    /**
     * Array of int32
     */
    public const ARRAY_INTEGER = 'int32[]';

    /**
     * Integer values larger than 2,147,483,647
     */
    public const LARGE_INT = 'int64';

    /**
     * Array of int64
     */
    public const LARGE_INT_ARRAY = 'int64[]';

    /**
     * Floating point / decimal numbers
     */
    public const FLOAT = 'float';

    /**
     * Array of floating point / decimal numbers
     */
    public const ARRAY_FLOAT = 'float[]';

    /**
     * true or false
     */
    public const BOOL   = 'bool';

    /**
     * Array of booleans
     */
    public const ARRAY_BOOL = 'bool[]';

    /**
     * Latitude and longitude specified as [lat, lng]
     */
    public const GEOPOINT = 'geopoint';

    /**
     * geopoint array
     */
    public const ARRAY_GEOPOINT = 'geopoint[]';

    /**
     * Speical characters
     */
    public const SPECIAL_TYPE = 'string*';

    /**
     * Use if it is dynamic type
     */
    public const AUTO = 'auto';
}
