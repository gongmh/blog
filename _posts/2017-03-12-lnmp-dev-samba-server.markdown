---
layout: post
title:  "samba服务"
date:  "2017-03-12 08:30:40 -0700"
category: lnmp
tags: samba
keywords: samba
description: ""
---


[Samba](https://zh.wikipedia.org/wiki/Samba)是种用来让UNIX系列的操作系统与微软Windows操作系统的SMB/CIFS（Server Message Block/Common Internet File System）网络协议做链接的自由软件。在UNIX系统上配置并开启samba服务后，在windows下映射相应的UNIX目录。这样，在windows下，就像想浏览普通windows路径一样访问UNIX资源。

本文以centos为例，配置samba服务。  

### 1 centos处理  
1. centos安装samba  
		`[gongmh@localhost ~]$ sudo yum install samba `  
2. 添加samba用户  
		`[gongmh@localhost ~]$ sudo smbpasswd -a gongmh`  
3. 配置samba  
		`[gongmh@localhost ~]$ echo -e "\n[mydisk]\n\tcomment = Home Directories\n\tbrowseable = no\n\twritable = yes\n\tpath=/home/gongmh\n\tvalid users =gongmh\n" >> /etc/samba/smb.conf`  
4. 启动samba服务  
		`[gongmh@localhost ~]$ sudo service smb start`  
		`[gongmh@localhost ~]$ service smb status`
5. 处理权限限制(防火墙问题根据实际情况配置)  
		```
		service iptables stop  
		setenforce 0
		```

### 2 windows处理  
1. 创建【映射网络驱动器】  
	![pic](https://gongmh.github.io/source/blog/pic/lnmp-samba-001.png)  

2. 配置  `\\ip\samba_name`  
	![pic](https://gongmh.github.io/source/blog/pic/lnmp-samba-002.png)  

3. 输入账号、密码连接服务  
	![pic](https://gongmh.github.io/source/blog/pic/lnmp-samba-003.png)  

(done)

（add 2017-06-24）
### 扩展
1. 验证samba是否成功安装
`smbclient -L host //Get a list of shares available on a host`  

2. 验证smb.conf文件是否正确
`testparm smb_conf_path -s`

