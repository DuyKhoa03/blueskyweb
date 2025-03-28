<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;
use Dotenv\Dotenv;

class GoogleService {
    private $client;

    public function __construct() {
        // Load biến môi trường
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->client = new Google_Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
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
