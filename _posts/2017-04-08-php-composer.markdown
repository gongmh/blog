---
layout: post
title:  "PHP依赖管理工具 Composer"
date:  "2017-04-08 10:18:40 -0700"
category: php
tags: php
keywords: php composer
description: ""
---

* TOC  
{:toc}  

### 1. 介绍  
Composer是PHP的依赖管理工具。我们只要声明项目中依赖的代码库，Composer能自动帮我们下载安装。

### 2. 安装  
安装Composer，并且安装到全局目录中。  
```  
$ curl -sS https://getcomposer.org/installer | php
$ mv composer.phar /usr/local/bin/composer
```  
这样就能直接使用composer命令。
```  
$ composer 
```  
### 3. 使用

3.1  创建`composer.json`文件  
在项目根目录中创建`composer.json`文件
``` json  
{
	"require": {
		"monolog/monolog": "1.0.*"
	}
}
```   

3.2 执行composer安装依赖  
在项目根目录执行命令：  
```  
$ composer install
```  
composer会自动帮我们下载依赖，结构如下:  
![pic](https://gongmh.github.io/source/blog/pic/php-composer-001.jpg)  
这时我们的项目中还会出现`composer.lock`文件，`composer.lock`文件中记录着依赖的版本号等信息。我们需要将该文件也提交到我们代码库中，这样任何人建立项目都是用的完全相同的依赖。  

3.3 使用已安装的库  
composer会自动帮我们生成`vendor/autoload.php`文件。我们只需引入这个文件，就能自动加载相应的库。
``` php  
require 'vendor/autoload.php';
```  
在项目中，我们就可以直接使用相应的库功能。
``` php  
$log = new Monolog\Logger('name');
$log->pushHandler(new Monolog\Handler\StreeamHandler('app.log', Monolog\Logger::WARNING));

$log->addWarning('Foo');
```  

### 4. 总结  
composer是php的依赖管理工具，本文简单介绍其基本使用，更多内容请阅读官方文档。