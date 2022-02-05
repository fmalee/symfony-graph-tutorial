<?php

namespace App\OAuth2\Provider;

use App\OAuth2\Provider\Exception\MicrosoftProviderException;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Microsoft extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * 认证基础网址
     * 
     * @var string
     */
    protected $authorityUrl = 'https://login.microsoftonline.com/common';

    /**
     * 认证端点
     * 
     * @var string
     */
    protected $authorizeEndpoint = '/oauth2/v2.0/authorize';

    /**
     * 令牌端点
     * 
     * @var string
     */
    protected $tokenEndpoint = '/oauth2/v2.0/token';

    /**
     * 资源用户资料
     * 
     * @var string
     */
    protected $resourceOwnerDetailsUrl = 'https://graph.microsoft.com/v1.0/me';

    /**
     * 权限范围
     * 
     * @var string|array
     */
    protected $scopes = ['openid,profile,offline_access,user.read'];

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->authorityUrl . $this->authorizeEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->authorityUrl . $this->tokenEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $uri = new Uri($this->resourceOwnerDetailsUrl);

        return (string) Uri::withQueryValue($uri, 'access_token', (string) $token);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes(): string|array
    {
        return $this->scopes;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new MicrosoftProviderException(
                $data['error']['message'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token): MicrosoftUser
    {
        return new MicrosoftUser($response);
    }
}
