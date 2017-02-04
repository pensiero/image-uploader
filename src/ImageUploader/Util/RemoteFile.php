<?php
namespace ImageUploader\Util;

class RemoteFile
{
    public static function checkIfExists($url)
    {
        // external check
        if (strpos($url, 'http') !== false) {

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $code == 200;
        }
        // internal check
        else {
            return file_exists(ltrim($url, '/'));
        }
    }

    // size of a remote file
    public static function calcSize($url, $user = "", $pw = "")
    {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        if (!empty($user) && !empty($pw)) {
            $headers = ['Authorization: Basic ' . base64_encode("$user:$pw")];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_exec($ch);
        curl_close($ch);
        $head = ob_get_contents();
        ob_end_clean();

        $regex = '/Content-Length:\s([0-9].+?)\s/';
        preg_match($regex, $head, $matches);

        return isset($matches[1]) ? $matches[1] : "unknown";
    }
}