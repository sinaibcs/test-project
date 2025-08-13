<?php

namespace App\Repositories;

use App\Interfaces\OAuthTokenRepositoryInterface;
use App\Models\ApiOAuthToken;
use Carbon\Carbon;

class OAuthTokenRepository implements OAuthTokenRepositoryInterface
{
    public function getToken(string $apiType): ?ApiOAuthToken
    {
        return ApiOAuthToken::where('api_type', $apiType)->first();
    }

    public function saveToken(array $data): ApiOAuthToken
    {
        return ApiOAuthToken::create($data);
    }

    public function updateToken(ApiOAuthToken $token, array $data): ApiOAuthToken
    {
        $token->update($data);
        
        return $token;
    }
}
