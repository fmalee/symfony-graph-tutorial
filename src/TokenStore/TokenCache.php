<?php

namespace App\TokenStore;

use Symfony\Component\HttpFoundation\RequestStack;

class TokenCache
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
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
  
      return $this->session->get('accessToken');
    }
}
