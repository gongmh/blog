---
layout: post
title:  "工具教程--sublime使用记录"
date:  "2017-03-16 13:18:40 -0800"
category: tools
tags: tools
keywords: sublime
description: ""
---  
* TOC
{:toc}
## 1. sublime2  

### 1.1 安装package control
通过`ctrl+~`或者`View->Show Console`调出控制台，然后输入一下内容回车，在线安装。
```  
import urllib2,os,hashlib; h = 'df21e130d211cfc94d9b0905775a7c0f' + '1e3d39e33b79698005270310898eea76'; pf = 'Package Control.sublime-package'; ipp = sublime.installed_packages_path(); os.makedirs( ipp ) if not os.path.exists(ipp) else None; urllib2.install_opener( urllib2.build_opener( urllib2.ProxyHandler()) ); by = urllib2.urlopen( 'http://packagecontrol.io/' + pf.replace(' ', '%20')).read(); dh = hashlib.sha256(by).hexdigest(); open( os.path.join( ipp, pf), 'wb' ).write(by) if dh == h else None; print('Error validating download (got %s instead of %s), please try manual install' % (dh, h) if dh != h else 'Please restart Sublime Text to finish installation')
```  


也可以通过下面方式离线安装`package control`  
1. Click the `Preferences > Browse Packages…` menu
2. **Browse up a folder** and then into the `Installed Packages/` folder
3. Download Package `Control.sublime-package` and copy it into the Installed Packages/ directory
4. **Restart** Sublime Text

### 1.2 sublime2 安装插件
	* shift + ctrl + p
	* 输入install package
	* 输入插件名回车就可以了自动安装

### 1.3 sublime2 删除插件
	* shift + ctrl + p
	* 输入remove package
	* 选择要删除的插件回车

## 2. sublime3 
### 2.1 安装package control
通过`ctrl+~`或者`View->Show Console`调出控制台，然后输入一下内容回车。
```  
import urllib.request,os,hashlib; h = 'df21e130d211cfc94d9b0905775a7c0f' + '1e3d39e33b79698005270310898eea76'; pf = 'Package Control.sublime-package'; ipp = sublime.installed_packages_path(); urllib.request.install_opener( urllib.request.build_opener( urllib.request.ProxyHandler()) ); by = urllib.request.urlopen( 'http://packagecontrol.io/' + pf.replace(' ', '%20')).read(); dh = hashlib.sha256(by).hexdigest(); print('Error validating download (got %s instead of %s), please try manual install' % (dh, h)) if dh != h else open(os.path.join( ipp, pf), 'wb' ).write(by)
```  