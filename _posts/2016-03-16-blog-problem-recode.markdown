---
layout: post
title:  "关于blog问题记录"
date:  "2016-03-16 13:18:40 -0800"
category: blog
tags: blog
keywords: blog
description: ""
---  

* TOC  
{:toc}  

## 1. 常用命令  
#### 1.1 **kramdown**常用命令  
1. 标题  
  # 一级标题  
  ## 二级标题  
  ###### 六级标题  
  
2. 引用  
  > 学而不思则罔思而不学则殆。  

3. 链接  
[百度](http://www.baidu.com)是一个搜索公司。

4. 图片  
![](https://help.github.com/assets/images/site/invertocat.png)

5. 代码块  
``` cpp
int main()  
{  
    printf("Hello World.\n");  
}
```
`$sKey = 'my_redis_key';`

6. 表格  
|---  
| Default aligned | Left aligned | Center aligned | Right aligned  
|-|:-|:-:|-:  
| First body part | Second cell | Third cell | fourth cell  
| Second line |foo | **strong** | baz  
| Third line |quux | baz | bar  
|---  
| Second body  
| 2 line  
|===  
| Footer row  
  
7. 删除线和下划线  
删除<del>hello world</del>  
删除~~del content~~  
下划线<u>下划内容</u>

8. 分割线  
* * *  
***  
*****  
- - -  
---------------------------------------  
 

9. 加粗和斜体  
*斜体文字*  
_斜体文字_  
**加粗文字**  
__加粗文字__  

10. 链接和邮箱  
  链接<http://www.baidu.com>链接  
  邮箱<git@github.com>邮箱  

11. 转义  
\*不是斜体\*

12. 上下标  
E = mc<sup>2</sup>  
Water: H<sub>2</sub>O  

13. 页内跳转  
点此[标签](#锚点)跳转。  
<a name="锚点" id="锚点">锚点</a>  

14. 目录  
```  
  \  * TOC  
  \  {:toc}  
  \  # Contents   
  \  {:.no_toc}  
  \  * Will be replaced with the ToC, excluding the "Contents" header  
  \  {:toc}  
  \  # H1 header  
  \  ## H2 header  
```  

15. 字体
<font color=#0099ff size=12 face="黑体">黑体</font>
## 2. 问题记录
#### 2.1 安装jekyll


## 3. 常用wiki
#### 3.1 kramdown教程
[kramdown](https://kramdown.gettalong.org/syntax.html)语法规则。
