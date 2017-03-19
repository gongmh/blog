---
layout: post
title:  "PHP扩展--xdebug"
date:  "2017-03-18 14:18:40 -0700"
category: php
tags: tools
keywords: xdebug
description: ""
---

xdebug是一个开源的php代码调试工具，支持运行时栈输出，运行时函数调用跟踪，代码覆盖率分析，性能数据采集以及内部状态显示
### 1.下载安装xdebug  
在[xdebug](http://xdebug.org/download.php)下载页，我们选择合适的版本进行安装，本文使用centos系列。  
1. 下载源码  
    `$ wget http://xdebug.org/files/xdebug-2.3.3.tgz`
2. 解压安装  
    ```
    $ tar zxvf xdebug-2.3.3.tgz
    $ cd xdebug-2.3.3
    $ phpize
    $ ./configure
    $ make && make install
    ```

### 2.配置xdebug  
修改php.ini文件
```
[Xdebug]
xdebug.profiler_enable=On
xdebug.trace_output_dir=/YOU_WANT_TO_SAVE_PATH/xdebug-output
xdebug.profiler_output_dir=/YOU_WANT_TO_SAVE_PATH/xdebug-output 
```
web服务需要重启php-fpm

### 3.使用xdebug  
现在每次调用php脚本，就会自动在`YOU_WANT_TO_SAVE_PATH/xdebug-output`生成相应的新能数据文件。

### 4. 分析性能文件  
性能文件可以使用专门的工具（win/mac下可以使用QCacheGrind，linux下可以使用KCacheGrind）。以QCacheGrind为例，界面左侧"Flat Profile"展示函数调用列表，`Incl.`包括子函数的调用时间，`Self`为去除子函数后自身消耗的时间。  

### 5. 示例  

示例代码
``` php
//test.php
<?php 
testXdebug(); 
function testXdebug() { 
    require_once('abc.php'); 
}

//abc.php
<?php
    echo "hello";

```  

profile分析效果：

![pic](https://gongmh.github.io/source/blog/pic/xdebug.png)  

[profile文件](://gongmh.github.io/source/blog/file/cachegrind.out.27457)


### 5. 扩展资料
1. xdebug文档  
    [xdebub-doc](https://xdebug.org/docs/index.php?action=profiler)
2. 360开源的PHP分析工具  
    [phptrace](https://github.com/Qihoo360/phptrace/blob/master/README_ZH.md)
               
               
               
