<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 15/01/2019
 * Time: 16:28
 */

class Log
{
    private static $instance = null;
    private static $log = [];
    const TYPE_INFO = 'info';
    const TYPE_ERROR = 'error';

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {

            self::$instance = new Log();
        }
        return self::$instance;
    }

    public static function addLog($info, $title, $type = self::TYPE_INFO)
    {
        self::$log[] = [
            'title' => $title,
            'info' => $info,
            'type' => $type,
            'datetime' => static::getDateTime(),
        ];
    }

    public static function getLog()
    {
        return static::$log;
    }

    private static function getDateTime()
    {
        return (new DateTime("now", new DateTimeZone('Europe/Lisbon')))->format('Y-m-d H:i:s');
    }

    public static function clearLog()
    {
        static::$log = [];
    }
}