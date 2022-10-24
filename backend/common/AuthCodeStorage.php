<?php

namespace common;

class AuthCodeStorage implements \OAuth2\Storage\AuthorizationCodeInterface
{
    public function getAuthorizationCode($code)
    {
        // TODO: Implement getAuthorizationCode() method.
    }

    public function setAuthorizationCode(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null
    ) {
        // TODO: Implement setAuthorizationCode() method.
    }

    public function expireAuthorizationCode($code)
    {
        // TODO: Implement expireAuthorizationCode() method.
    }
}
