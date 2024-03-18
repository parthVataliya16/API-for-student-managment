<?php

class LoginWithGoogleService
{
    private $client;

    public function __construct()
    {
        global $googleCredentials;
        $this->client = new Google\Client();
        $this->client->setClientId($googleCredentials['clientId']);
        $this->client->setClientSecret($googleCredentials['secretKey']);
        $this->client->setRedirectUri($googleCredentials['redirectUri']);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->addScope("email");
        $this->client->addScope("profile");
    }
    
    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    public function userData($token)
    {
        $this->client->setAccessToken($token);
        $googleOAuth = new Google\Service\Oauth2($this->client);
        return $googleOAuth->userinfo->get();
    }

    public function getAccessTokenFromAuthCode($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        return $this->userData($token);
    }

    public function getAccessTokenFromRefreshToken()
    {
        $refreshTokenSaved = $this->client->getRefreshToken();
        $this->client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
        $updatedAccessToken = $this->client->getAccessToken();
        $updatedAccessToken['refresh_token'] = $refreshTokenSaved;
        // $accessToken = $refreshTokenSaved;
        // $this->client->setAccessToken($accessToken);
        return $this->userData($updatedAccessToken);
    }
}
?>