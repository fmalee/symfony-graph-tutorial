<?php

namespace App\OAuth2;

use App\OAuth2\Provider\Microsoft as Provider;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Microsoft
{
    private $params;
    private $router;

    public function __construct(ContainerBagInterface $params, UrlGeneratorInterface $router)
    {
        $this->params = $params;
        $this->router = $router;
    }

    public function getProvider(?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null): Provider
    {
        return new Provider([
            'clientId' => $clientId ?? $this->params->get('client_id'),
            'clientSecret' => $clientSecret ?? $this->params->get('client_secret'),
            'redirectUri' => $this->getRedirectUri(),
            'scopes' => $this->params->get('graph_scopes'),
        ]);
    }

    public function getRedirectUri(): string
    {
        return $this->router->generate($this->params->get('callback_route'), [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
