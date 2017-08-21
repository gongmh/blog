---
layout: post
title:  "Linux基本命令--awk"
date:  "2017-08-21 11:25:40 +0800"
category: linux
tags: linux
keywords: awk
description: ""
---

* TOC  
{:toc}

### 1. 基本概念
awk是一种[样式扫描与处理工具](http://www.chinaunix.net/old_jh/7/16985.html)
awk是行处理器，相比较屏幕处理的优点，在处理庞大文件时不会出现内存溢出或是处理缓慢的问题，通常用来格式化文本信息。awk处理过程是依次对每一行进行处理，然后输出。

### 2. 基本应用

```
 awk '{ sum += $1 }; END { print sum }' file
 awk -F: '{ print $1 }' /etc/passwd
```

### 3. 工作流程

awk一次读取一行文本，按输入分隔符进行切片，切成多个组成部分，将每片直接保存在内建的变量中，$1，$2，$3，...。引用指定的变量，可以显示指定段，或者多个段。如果需要显示全部的，需要使用$0来引用。可以对单个片段进行判断，也可以对所有断进行循环判断。默认分隔符为空白字符。

### 4. 命令详解

#### 4.1 用法

```
 awk [ -F fs ] [ -v var=value ] [ 'prog' | -f progfile ] [ file ...  ]
```

* `-F` 	指定分隔符，默认是空白字符
* `-v` 	定义变量
* `-f`	指定脚本
* `'prog'` 	代码块
* `file` 	待处理的文件

#### 4.2 代码块详解

代码块格式：

```
 'BEGIN{} //{command1; command2} END{}'
```

* `BEGIN`		初始化代码块，在对每一行进行处理之前，初始化代码，主要是引用全局变量，设置FS分隔符
* `//`			匹配代码块，可以是字符串或正则表达式
* `{}`			命令代码块，包含一条或多条命令
* `;`			多条命令使用分号分隔
* `END`			结尾代码块，在对每一行进行处理之后再执行的代码块，主要是进行最终计算或输出结尾摘要信息

#### 4.3 内建变量

* `ARGC`			命令行参数个数
* `ARGV`			命令行参数排列
* `ENVIRON`			支持队列中系统环境变量的使用
* `FILENAME`		awk命令所处理的文件的名称
* `FNR`				对每个文件进行行数单独编号
* `FS`				设置输入域分隔符，等价于命令行 -F选项
* `NF`				字段个数
* `NR`				文件中的行数
* `OFS`				输出域分隔符
* `ORS`				输出记录分隔符
* `RS`				控制记录分隔符
* `$0` 				表示整个当前行
* `$1` 				每行第一个字段
* ...				...
* `$NF` 			每行最后一个字段

### 5. 典型应用

(1)基本变量使用及输出

```
awk -F: '{print NR,NF$1,"\t",$0}' /etc/passwd 	//输出行号，每行字段数，每行第一个字段，整行的值
awk -F: 'NR==5 || NR==6{print}'  /etc/passwd 	//输出第5行和第6行
awk -F: 'NR!=5 && NR!=6{print}'  /etc/passwd 	//输出除了第5行和第6行
```

(2)使用匹配代码块（字符匹配）

```
awk '/root/{print $0}' /etc/passwd			//输出匹配root的行
awk '!/root/{print $0}' /etc/passwd			//输出不匹配root的行
awk '/root|mail/{print}' /etc/passwd		//输出匹配root或者mail的行
```

(3)条件语句

```
 awk -F: '$3>100 {print $0}' /etc/passwd
 awk -F: '{if($3>100){print $1}}' /etc/passwd
 awk -F: '$3+$4 > 200' /etc/passwd
 awk -F: '{if($3>100) print "large"; if($3>110) print "e large"}' /etc/passwd
```

(4)输出结果重定向

```
awk 'NR!=1{print > "./filename"}'  /etc/passwd
awk 'NR!=1{print}'  /etc/passwd > ./filename
```

（5）格式化输出

```
awk -F: '{printf "%-8s %-10s %-10s\n",1,2,$3}' /etc/passwd
```

（6）使用数组

```
netstat -anp|awk 'NR!=1{a[$6]++} END{for (i in a) print i,"\t",a[i]}'
```

(7)其他

```
ls -l|awk 'BEGIN{sum=0} !/^d/{sum+=$5} END{print "total size is:",int(sum/1024),"KB"}'	//统计当前目录下除了文件夹所有文件之和的大小

netstat -anp|awk '/LISTEN|CONNECTED/{sum[$6]++} END{for (i in sum) printf "%-10s %-6s %-3s \n", i," ",sum[i]}'	//统计状态为LISTEN和CONNECT的连接数量
```