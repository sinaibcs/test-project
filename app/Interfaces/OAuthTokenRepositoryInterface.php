<?php

namespace App\Interfaces;

use App\Models\ApiOAuthToken;

interface OAuthTokenRepositoryInterface
{
    public function getToken(string $apiType): ?ApiOAuthToken;
    public function saveToken(array $data): ApiOAuthToken;
    public function updateToken(ApiOAuthToken $token, array $data): ApiOAuthToken;
}
