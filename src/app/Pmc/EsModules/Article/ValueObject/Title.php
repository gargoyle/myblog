<?php
namespace Pmc\EsModules\Article\ValueObject;

use Pmc\ObjectLib\BasicString;

/**
 * Article titles can be upto 100 characters.
 */
class Title extends BasicString
{
    public function __construct($value)
    {
        parent::__construct($value, false, 100);       
    }
}
