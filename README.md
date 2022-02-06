# Microsoft Graph Tutorial

本教程指导你如何构建使用 **Microsoft Graph API** 检索用户的日历信息的基于 **Symfony 6.04** 的 PHP Web 应用。

## 目的

微软 Graph的[官方 PHP 教程](https://docs.microsoft.com/zh-cn/graph/tutorials/php)是基于Laravel进行教学的。

而本人更习惯于Symfony。

同时换个框架就可以不用照搬教程内容，进一步理解 Graph 的构架。

Symfony版教程的步骤都按照官方的走，只是换了个编程框架而已。

## 要求

- PHP：>=8.0.2
- PHP扩展：Ctype、iconv、PCRE、Session、SimpleXML以及Tokenizer
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download) 或 Nginx等Web服务器

## 安装

```shell
# 创建教程目录
$ mdkdir symfony-graph-tutorial
$ cd symfony-graph-tutorial
# 克隆代码库到临时目录
$ git clone git@github.com:fmalee/symfony-graph-tutorial.git tmp
# 移动git版本库到教程目录
$ mv tmp/.git . 
# 删除临时目录
$ rm -rf tmp 
# 展开最新版本代码到工作区
$ git reset --hard 
# 用 composer 安装依赖
$ composer install -vvv # 
# 设置框架缓存目录的权限
$ chmod -R 777 var
```

## 访问

### 自带Web服务器

```shell
$ cd symfony-graph-tutorial
$ symfony server:start
```

访问 `http://localhost:8000/`

### Valet

```shell
$ cd symfony-graph-tutorial
$ valet link
```

访问 `http://symfony-graph-tutorial.test`

## 使用

原教程比较详细，一大步骤会细分小步骤，然后会出现修改源代码的情况。

所以为了能重现当时的代码，特意给每个步骤都添加了标签，比如 `step3.1`。

如果要对比教程，阅读到教程对应步骤时，可以在Git中检出对应的标签，以重现对应步骤的代码。
