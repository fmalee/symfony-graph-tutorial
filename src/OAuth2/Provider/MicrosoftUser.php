<?php

namespace App\OAuth2\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MicrosoftUser implements ResourceOwnerInterface
{
    /**
     * 原始响应
     *
     * @var array
     */
    protected $response;

    /**
     * MicrosoftUser构造函数
     *
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * 获取用户ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getResponseValue('id');
    }

    /**
     * 获取用户邮箱
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getResponseValue('email');
    }

    /**
     * 获取用户名称
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getResponseValue('displayName');
    }

    /**
     * 获取用户名字
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->getResponseValue('givenName');
    }

    /**
     * 获取用户姓氏
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->getResponseValue('surname');
    }

    /**
     * 获取用户手机
     *
     * @return string|null
     */
    public function getMobilePhone(): ?string
    {
        return $this->getResponseValue('mobilePhone');
    }

    /**
     * 获取用户办公室位置
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->getResponseValue('officeLocation');
    }

    /**
     * 获取用户首选语言
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->getResponseValue('preferredLanguage');
    }

    /**
     * 获取用户数据数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }

    /**
     * 从响应中获取指定键的数据
     */
    private function getResponseValue($key): ?string
    {
        return $this->response[$key] ?? null;
    }
}
