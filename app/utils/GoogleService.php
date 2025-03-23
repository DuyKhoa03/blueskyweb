<?php
require_once 'vendor/autoload.php';
use Google\Service\Oauth2 as Google_Service_Oauth2;

class GoogleService {
    private $client;

    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setClientId('375836455855-51amv6t3tfkuldpb3ckgm9ragg7l6pcs.apps.googleusercontent.com');
        $this->client->setClientSecret('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');
        $this->client->setRedirectUri('http://localhost/blueskyweb/account/googleCallback');
        $this->client->addScope("email");
        $this->client->addScope("profile");
        $this->client->setPrompt('select_account');
    }

    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }
    

    public function fetchUserData($code) {
        $this->client->authenticate($code);
        $_SESSION['access_token'] = $this->client->getAccessToken();
        $oauth2 = new Google_Service_Oauth2($this->client);
        return $oauth2->userinfo->get();
    }
}
