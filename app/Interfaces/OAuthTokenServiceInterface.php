<?php

namespace App\Interfaces;

use App\Models\ApiOAuthToken;

interface OAuthTokenServiceInterface
{
    public function getValidToken(string $apiType): ?ApiOAuthToken;
    public function refreshToken(string $apiType): ApiOAuthToken;
}
