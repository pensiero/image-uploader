<?php
namespace ImageUploader\Util;

use ImageUploader\Exception\NotProvidedException;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Mcrypt;

class Crypt
{
    /**
     * @var BlockCipher
     */
    protected $blockCypher;

    public function __construct()
    {
        if (!getenv('CRYPT_KEY')) {
            throw new NotProvidedException('CRYPT_KEY env variable must be provided in order to encrypt/decrypt');
        }

        $this->blockCipher = new BlockCipher(new Mcrypt(['algo' => 'aes']));
        $this->blockCipher->setKey(getenv('CRYPT_KEY'));
    }

    /**
     * Encrypt a string
     *
     * @param $string
     *
     * @return string
     * @throws \Exception
     */
    public function encrypt($string)
    {
        if (empty($string)) {
            throw new \Exception('String can\'t be empty in order to encrypt it');
        }

        return $this->blockCipher->encrypt($string);
    }

    /**
     * Decrypt a string
     *
     * @param $string
     *
     * @return bool|string
     * @throws \Exception
     */
    public function decrypt($string)
    {
        if (empty($string)) {
            throw new \Exception('String can\'t be empty in order to decrypt it');
        }

        return $this->blockCipher->decrypt($string);
    }

    /**
     * Encrypt an array
     *
     * @param $array
     *
     * @return string
     * @throws \Exception
     */
    public function encryptArrayIntoString($array)
    {
        if (empty($array)) {
            throw new \Exception('Array can\'t be empty in order to encrypt it');
        }

        return $this->encrypt(json_encode($array));
    }

    /**
     * Decrypt an array
     *
     * @param $string
     *
     * @return mixed
     * @throws \Exception
     */
    public function decryptArrayFromString($string)
    {
        if (empty($string)) {
            throw new \Exception('Array can\'t be empty in order to decrypt it');
        }

        return json_decode($this->decrypt($string), true);
    }

}