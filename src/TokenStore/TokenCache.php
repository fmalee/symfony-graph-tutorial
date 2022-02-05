<?php

namespace App\TokenStore;

use App\OAuth2\Microsoft;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RequestStack;

class TokenCache
{
    private $session;
    private $microsoft;

    public function __construct(RequestStack $requestStack, Microsoft $microsoft)
    {
        $this->session = $requestStack->getSession();
        $this->microsoft = $microsoft;
    }

    /**
     * 储存令牌到会话
     */
    public function storeTokens($accessToken, $user): void
    {
        $this->session->set('accessToken', $accessToken->getToken());
        $this->session->set('refreshToken', $accessToken->getRefreshToken());
        $this->session->set('tokenExpires', $accessToken->getExpires());
        $this->session->set('userName', $user->getDisplayName());
        $this->session->set('userEmail', $user->getMail() ? $user->getMail() : $user->getUserPrincipalName());
        $this->session->set('userTimeZone', $user->getMailboxSettings()->getTimeZone());
    }

    /**
     * 更新令牌
     */
    public function updateTokens($accessToken): void
    {
        $this->session->set('accessToken', $accessToken->getToken());
        $this->session->set('refreshToken', $accessToken->getRefreshToken());
        $this->session->set('tokenExpires', $accessToken->getExpires());
    }

    /**
     * 清除令牌
     */
    public function clearTokens(): void
    {
        $this->session->remove('accessToken');
        $this->session->remove('refreshToken');
        $this->session->remove('tokenExpires');
        $this->session->remove('userName');
        $this->session->remove('userEmail');
        $this->session->remove('userTimeZone');
    }

    /**
     * 获取访问令牌
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        // 检查令牌是否存在
        if (!$this->session->has('accessToken') ||
            !$this->session->has('refreshToken') ||
            !$this->session->has('tokenExpires')) {
            return '';
        }

        // 检查令牌是否过期
        // 获取当前时间（+5分钟是考虑时差）
        $now = time() + 300;
        // 令牌已（快）过期，需要更新
        if ($this->session->get('tokenExpires') <= $now) {
            // 初始化OAuth客户端
            $oauthClient = $this->microsoft->getProvider();

            try {
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $this->session->get('refreshToken')
                ]);

                // 储存新令牌
                $this->updateTokens($newToken);

                return $newToken->getToken();
            } catch (IdentityProviderException $e) {
                return '';
            }
        }

        // 令牌还在有效期内，直接返回
        return $this->session->get('accessToken');
    }
}
