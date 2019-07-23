<?php
namespace Pmc\EsModules\User\ValueObject;

use InvalidArgumentException;
use Pmc\ObjectLib\BasicString;
use function mb_stripos;


/**
 * Email address not be blank and a max of 250 characters.
 */
class EmailAddress extends BasicString
{
    public function __construct($value)
    {
        parent::__construct($value, false, 250);
        if (mb_stripos($value, '@') === false) {
            throw new InvalidArgumentException("Email addresses must contain an @.");
        }
    }
}
