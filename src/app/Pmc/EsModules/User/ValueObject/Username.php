<?php

namespace Pmc\EsModules\User\ValueObject;

use Pmc\ObjectLib\BasicString;

/**
 * Usernames can be a max of 50 chars and must not be empty
 */
class Username extends BasicString
{
    public function __construct($value)
    {
        parent::__construct($value, false, 50);       
    }
}
