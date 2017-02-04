<?php
namespace ImageUploader\Util\Crypt;

class CryptArray extends CryptUnsafe
{
    const SEPARATOR = '###';

    public static function encrypt($array, $key, $encode = false)
    {
        return parent::encrypt(implode($array, self::SEPARATOR), getenv('CRYPT_KEY'), true);
    }

    public static function decrypt($message, $key, $encoded = false)
    {
        return explode(self::SEPARATOR, parent::decrypt($message, $key, $encoded));
    }
}