<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 26/11/2018
 * Time: 15:21
 */

class Auth {

    private $facebook;
    private $helper;

    public function __construct(\Facebook\Facebook $fb)
    {
        $this->facebook = $fb;
        $this->helper = $this->facebook->getRedirectLoginHelper();
    }

    public function getRedirectURL()
    {
        $loginUrl = '';
        try {
            $loginUrl = $this->helper->getLoginUrl(Params::REDIRECT_URL, Params::PERMISSIONS);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            Utils::printError($e->getMessage());

        } catch (\Facebook\Exceptions\FacebookSDKException $e) {

            Utils::printError($e->getMessage());
        }
        //Utils::printInfo(htmlspecialchars($loginUrl));
        return $loginUrl;
    }

    public function getAccessToken()
    {
        $accessToken = '';
        try {
            $accessToken = $this->helper->getAccessToken();

        } catch(Facebook\Exceptions\FacebookResponseException $e) {

            Utils::printError('Graph returned an error: ' . $e->getMessage());

        } catch(Facebook\Exceptions\FacebookSDKException $e) {

            Utils::printError('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (empty($accessToken)) {

            if ($this->helper->getError()) {

                header('HTTP/1.0 401 Unauthorized');
                $error  = "Error: " . $this->helper->getError() . "\n";
                $error .= "Error Code: " . $this->helper->getErrorCode() . "\n";
                $error .= "Error Reason: " . $this->helper->getErrorReason() . "\n";
                $error .= "Error Description: " . $this->helper->getErrorDescription() . "\n";

                Utils::printError($error);

            } else {

                header('HTTP/1.0 400 Bad Request');
                Utils::printError('Bad request');
            }
            Utils::printError('Unknown error...');
        }
        return $accessToken;
    }
}