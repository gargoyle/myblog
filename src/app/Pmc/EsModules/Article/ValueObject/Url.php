<?php
namespace Pmc\EsModules\Article\ValueObject;

use Pmc\ObjectLib\BasicString;

/**
 * Article slugs can be upto 255 characters, all lower case
 */
class Url extends BasicString
{
    public function __construct($value)
    {
        $value = str_replace(' ', '+', strtolower($value));
        parent::__construct($value, false, 255);
    }
}
