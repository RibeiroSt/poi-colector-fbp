<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 10/01/2019
 * Time: 16:22
 */

class Registry {

    private static $pdo = null;
    private static $coords = [];

    public static function getConnection()
    {
        if (static::$pdo === null) {

            static::setPDO();
        }
        return static::$pdo;
    }

    private static function setPDO()
    {
        $cnn_str = static::loadDatabaseParams();
        try{
            static::$pdo = new PDO($cnn_str);
            static::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            //static::$pdo->setAttribute(PDO::ATTR_AUTOCOMMIT ,0);

        } catch (Exception $e) {

            Utils::printError($e->getMessage());
        }
    }

    private static function loadDatabaseParams()
    {
        try{
            $params = parse_ini_file('config/default-ds.ini');

        } catch (Exception $e) {

            Utils::printError($e->getMessage());
        }

        if ($params === false) {

            Utils::printError("Error reading database configuration file");
        }

        $connection_string = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['host'],
            $params['port'],
            $params['database'],
            $params['user'],
            $params['password']
        );
        return $connection_string;
    }

    public static function setCoords($coords)
    {
        self::$coords = $coords;
    }

    public static function getCoords()
    {
        return self::$coords;
    }


}