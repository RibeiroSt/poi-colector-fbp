<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 13/12/2018
 * Time: 15:27
 */

class Dataget
{
    private $facebook;
    private $facade_dao;
    private $results = [];

    private $control_fields = [
        'datetime' => '',
        'url' => '',
        'coords' => '',
    ];

    public function __construct(\Facebook\Facebook $fb)
    {
        $this->facebook = $fb;
        $this->facade_dao = new FacadeDAO();
    }

    public function getPlaces($token, $wait)
    {
        $this->results = [];
        $this->control_fields['coords'] = $this->facade_dao->getNextCoordinate();

        $custom_params = [
            Params::CENTER_KEY => [
                $this->control_fields['coords'][Params::LATITUDE_KEY],
                $this->control_fields['coords'][Params::LONGITUDE_KEY],
            ],
        ];
        $this->requestPlaces($token, $custom_params, '', $wait);
    }

    private function requestPlaces($token, $custom_params = [], $url = '', $wait = true)
    {
        $response = '';
        try {
            $url = ($url !== '') ? $url : $this->getURL($custom_params);

            $this->defineDateTime();
            $this->logInfo($url);

            $response = $this->facebook->get($url, $token);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            Utils::printError($e->getMessage());

        } catch (\Facebook\Exceptions\FacebookSDKException $e) {

            Utils::printError($e->getMessage());
        }
        $this->addResults($this->decodeBody($response));

        if ($wait) {

            $this->doPause();
        }

        if (!empty($body['paging']['next'])) {

            $newUrl = $this->extractNextCallURL($body['paging']['next']);
            $this->requestPlaces($token, $custom_params, $newUrl);
        } else {

            $this->persistData();
        }
    }

    private function doPause()
    {
        $min = Params::MIN_SLEEP_TIME;
        $max = Params::MAX_SLEEP_TIME;

        $sleep_time = rand($min, $max);

        // wait before the next call
        sleep($sleep_time);
    }

    private function decodeBody($response)
    {
        if (!is_object($response)) {

            $msg  = 'The request returned a string instead a vector as a request body.' . PHP_EOL;
            $msg .= 'Response: ' . PHP_EOL;
            $msg .= print_r($response, true);

            Utils::printError($msg, 'Error', false);

            return null;
        }
        $body = [];
        try{
            $body = $response->getDecodedBody();

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            $msg  = "Error trying to decode response body." . PHP_EOL;
            $msg .= "Details: " . print_r($e, true);

            Utils::printError($msg, 'Error', false);
        }
        return $body;
    }

    private function addResults($body)
    {
        if (empty($body['data'])) {

            $msg  = 'The request returned an empty data vector.' . PHP_EOL;
            $msg .= 'Body: ' . PHP_EOL;
            $msg .= print_r($body, true) . PHP_EOL;
            $msg .= 'Coords: ' . PHP_EOL;
            $msg .= print_r($this->control_fields['coords'], true);

            Utils::printInfo($msg, 'Error');

            return;
        }
        $this->results[] = $body['data'];
    }

    private function persistData()
    {
        $control_fields = $this->control_fields;
        $control_fields['datetime'] = $control_fields['datetime']->format('Y-m-d H:i:s');
        $control_fields['coords'] = implode(',', array_values($control_fields['coords']));

        $this->facade_dao->insertPlaces($this->results, $control_fields);
    }

    private function getURL($custom_params)
    {
        $this->control_fields['url'] = Utils::getURL(Params::PLACE_SEARCH_KEY, $custom_params);

        return $this->control_fields['url'];
    }

    private function defineDateTime()
    {
        $this->control_fields['datetime'] = new DateTime("now", new DateTimeZone('Europe/Lisbon'));
    }

    private function extractNextCallURL($next)
    {
        // try to split the 'next' param returned into 'paging' array for creating the url to the next call
        $partUrl = explode('?', $next);

        if (empty($partUrl[1])) {

            Utils::printError('Error geting next results: The \'url\' param for next call was not found.');
        }
        $newUrl = 'search?' . $partUrl[1];
        return $newUrl;
    }

    private function logInfo($url)
    {
        $info = 'New Places Request >> ';
        $info .= $this->control_fields['datetime']->format("D M j Y, G:i:s") . Params::CR_CHARACTER;
        $info .= 'URL >> ' . $url . Params::CR_CHARACTER . Params::CR_CHARACTER;

        Utils::printInfo($info);
    }
}