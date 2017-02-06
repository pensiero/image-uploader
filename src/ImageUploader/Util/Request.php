<?php declare(strict_types=1);
namespace ImageUploader\Util;

class Request
{
    /**
     * Return server address
     *
     * @return string
     */
    public static function serverUrl()
    {
        $scheme = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            ? $_SERVER['HTTP_X_FORWARDED_PROTO']
            : $_SERVER['REQUEST_SCHEME'];

        $hostname = isset($_SERVER['HTTP_HOST'])
            ? $_SERVER['HTTP_HOST']
            : $_SERVER['SERVER_NAME'];

        return $scheme . '://' . $hostname;
    }

}