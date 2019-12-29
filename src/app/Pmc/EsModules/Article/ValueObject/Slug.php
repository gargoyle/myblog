<?php
namespace Pmc\EsModules\Article\ValueObject;

use Pmc\ObjectLib\BasicString;

/**
 * Article slugs can be upto 100 characters, all lower case, spaces are converted to hyphens
 */
class Slug extends BasicString
{
    public function __construct($value)
    {
        $value = str_replace(' ', '-', strtolower($value));
        parent::__construct($value, false, 100);
    }
}
