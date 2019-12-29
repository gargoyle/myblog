<?php
namespace Pmc\EsModules\User\ValueObject;

use Pmc\EsModules\User\Exception\BadPassphraseException;
use Pmc\ObjectLib\BasicString;
use function mb_strlen;


/**
 * Passwords have minimum length and complexity requirements.
 */
class Passphrase extends BasicString
{
    const MIN_LENGTH = 10;

    public function __construct(string $value)
    {
        $this->checkStrength($value);
        parent::__construct($value, false);
    }
    
    private function checkStrength(string $value): bool
    {
        if (mb_strlen($value) < self::MIN_LENGTH) {
            throw new BadPassphraseException("Passphrase is too short! It must be a minimum of ".self::MIN_LENGTH." characters. Try using multiple words.");
        }

        return true;
    }
    
    public function getHash(): string
    {
        return password_hash(sha1($this->value), PASSWORD_DEFAULT);
    }
    
    public function verify($hashToCheck): bool
    {
        return password_verify(sha1($this->value), $hashToCheck);
    }
}
