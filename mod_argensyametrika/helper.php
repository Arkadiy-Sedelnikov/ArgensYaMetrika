<?php
/**
 * @package        mod_argensyametrika
 * @copyright    Copyright (c) 2015 Arkadiy Sedelnikov
 * @license    GNU General Public License version 3 or later
 */


defined('_JEXEC') or die;

class ModArgensyametrikaHelper
{
    public static function open_http($url, $token)
    {
        if (!function_exists('curl_init')) {
            die('CURL not found');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$token));
        $result = curl_exec($ch);
        curl_close($ch);


        return $result;
    }
}
