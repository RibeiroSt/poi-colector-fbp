<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 26/11/2018
 * Time: 15:21
 */

class Main {

    private $auth;
    private $facebook;
    private $dataget;
    private $coordinates;

    public function __construct()
    {
        $this->facebook = new \Facebook\Facebook(Params::APP_PARAMS);
        $this->auth = new Auth($this->facebook);

        $this->dataget = new Dataget($this->facebook);
        $this->coordinates = new Coordinate();
    }

    public function getRedirectURL()
    {
        return $this->auth->getRedirectURL();
    }

    public function getAuthToken()
    {
        return $this->auth->getAccessToken();
    }

    public function createCoords()
    {
        $this->coordinates->generateInitialCoordinates();
    }

    public function getPlaces($token, $wait = true)
    {
        $this->dataget->getPlaces($token, $wait);
    }

    public function printError($error)
    {
        Utils::printError($error);
    }
}