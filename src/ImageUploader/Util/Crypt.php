<?php
namespace ImageUploader\Util;

use ImageUploader\Exception\NotProvidedException;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Mcrypt;

class Crypt
{
    private function base64urlEncodeClean($data)
    {
        return rtrim(strtr($data, '+/', '-_'), '=');
    }

    private function base64urlDecodeClean($data, $strict = false)
    {
        return str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
    }

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

        return $this->base64urlEncodeClean($this->blockCipher->encrypt($string));
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

        return $this->blockCipher->decrypt($this->base64urlDecodeClean($string));
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