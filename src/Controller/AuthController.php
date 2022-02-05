<?php

namespace App\Controller;

use App\OAuth2\Microsoft;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * 认证状态的名称
     *
     * @var string
     */
    protected $stateName = 'oauthState';

    /**
     * 认证登录
     *
     * @Route("/signin", name="signin")
     */
    public function signin(Microsoft $microsoft)
    {
        $oauthClient = $microsoft->getProvider();

        // 获取认证网址
        $authUrl = $oauthClient->getAuthorizationUrl();

        // 记录认证状态码
        $this->getSession()->set($this->stateName, $oauthClient->getState());

        // 跳转到微软认证页面
        return $this->redirect($authUrl);
    }

    /**
     * 认证回调
     *
     * @Route("/callback", name="callback")
     */
    public function callback(Request $request, Microsoft $microsoft)
    {
        $session = $this->getSession();

        // 如果会话中没有预期状态
        if (!($expectedState = $session->get($this->stateName))) {
            return $this->redirectToRoute('home');
        }
        $session->remove($this->stateName);

        $providedState = $request->query->get('state');
        dump($expectedState, $providedState);

        if (!$providedState || $expectedState != $providedState) {
            return $this->redirectToHome('无效的认证状态', '提供的认证状态与预期值不匹配');
        }

        $authCode = $request->query->get('code');
        dump($authCode);
        if (isset($authCode)) {
            // 初始化OAuth客户端
            $oauthClient = $microsoft->getProvider();
      
            try {
              // 创建令牌请求
              $accessToken = $oauthClient->getAccessToken('authorization_code', [
                'code' => $authCode
              ]);
      
              // 临时测试！
              return $this->redirectToHome('接收到访问令牌', $accessToken->getToken());
            } catch (IdentityProviderException $e) {
                return $this->redirectToHome('请求访问令牌时出错', json_encode($e->getResponseBody()));
            }
        }

        return $this->redirectToHome($request->query->get('error'), $request->query->get('error_description'));
    }

    /**
     * 注销认证
     *
     * @Route("/signout", name="signout")
     */
    public function signout()
    {
        // TODO
    }

    public function redirectToHome(string $info, string $detail)
    {
        $this->addFlash('error', ['info' => $info, 'detail' => $detail]);
        return $this->redirectToRoute('home');
    }
}
