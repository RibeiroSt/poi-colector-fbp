<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 12/12/2018
 * Time: 11:46
 */

class Utils
{

    public static function printError($error, $title = 'Error', $save_error = true)
    {
        $error = self::infoToString($error);
        Log::addLog($error, $title, Log::TYPE_ERROR);

        self::printData($error, $title);

        if ($save_error) {

            $logdao = new LogDAO(Registry::getConnection());
            $logdao->persist();

            return;
        }
        try {
            $pdo = Registry::getConnection();
            $pdo->rollback();

        } catch (PDOException $e) {
        }
        Registry::setCoords([]);
        exit;
    }

    public static function printInfo($info, $title = 'Info')
    {
        $info = self::infoToString($info);
        Log::addLog($info, $title, Log::TYPE_INFO);

        self::printData($info, $title);
    }

    private static function printData($info, $title = null)
    {
        echo '<pre>';

        if ($title !== null) {

            echo Params::CR_CHARACTER . Params::CR_CHARACTER . $title . ':';
        }
        echo Params::CR_CHARACTER;
        print_r($info);
        //var_dump($info);

        echo '</pre>';
    }

    public static function getURL($key, $custom_params = [])
    {
        $base_url = '';

        if (!in_array($key, Params::VALID_URL_LIST)) {

            return $base_url;
        }
        $base_url = Params::URL_LIST[$key];
        return $base_url . self::getParams($key, $custom_params);
    }

    private static function getParams($url_key, $custom_params)
    {
        $params = '';

        if (is_array(Params::REQUEST_PARAMS[$url_key]) && count(Params::REQUEST_PARAMS[$url_key]) > 0) {

            $first = true;

            foreach (Params::REQUEST_PARAMS[$url_key] as $key => $value) {

                $params .= ($first) ? '?' : '&';

                if (!is_array($value)) {

                    $value = urlencode($value);
                } else {

                    $value = implode(',', $value);
                }
                if ($value === '') {

                    $value = (!empty($custom_params[$key])) ? $custom_params[$key] : 'null';

                    if (is_array($value)) {

                        $value = implode(',', $value);
                    }
                }
                $params .= $key . '=' . $value;
                $first = false;
            }
        }
        return $params;
    }

    public static function execSQL(
        $pdo,
        $sql,
        $table_name,
        $seq_name,
        $fetch = false,
        $params = [],
        $print_response = false,
        $print_details = true
    )
    {
        if ($print_details) {

            $print_details = 'SQL: ' . PHP_EOL . $sql . PHP_EOL;
            $print_details .= 'PARAMS ' . PHP_EOL . print_r($params, true) . PHP_EOL;

            static::printInfo($print_details);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        static::printInfo('Number of affected rows: ' . $stmt->rowCount(), 'Info');
        $row = [];

        if ($fetch) {
            $row = $stmt->fetch();

        } else {
            if ($print_response) {

                $info = 'Last insert ID (' . $table_name . '): ' . $pdo->lastInsertId($seq_name);
                Utils::printInfo($info);
            }
        }
        return $row;
    }

    private static function infoToString($info)
    {
        return (is_array($info) || is_object($info)) ? print_r($info, true) : $info;
    }
}