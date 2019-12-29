<?php
namespace Pmc\EsModules\User\ValueObject;

use Pmc\ObjectLib\BasicString;

/**
 * Full names must not be blank and a max of 100 characters.
 */
class FullName extends BasicString
{
    public function __construct($value)
    {
        parent::__construct($value, false, 100);       
    }
}
