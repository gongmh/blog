---
layout: post
title:  "golang基础-07-slice的截取和追加"
date:   2019-04-28 19:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}

熟悉slice底层实现的同学都知道，slice底层结构实包含的是指针、长度和容量，指针指向底层的数据数组。当我们对切片进行截取或追加时，如果不注意截取和追加的基本规则，可能会有意想不到的结果。下面先通过两个例子来看看问题，然后我们再具体分析。

例1 [（运行）](https://www.gongmh.com/tools/s?id=d3zdWA_Mg)

``` go
	s := []int{1, 2, 3, 4, 5}
	a := s[2:3]  // diff

	a = append(a, 1)

	fmt.Println(s, a)
```

例2 [（运行）](https://www.gongmh.com/tools/s?id=87PFWAlGR)

``` go
	s := []int{1, 2, 3, 4, 5}
	a := s[2:3:3]  //diff

	a = append(a, 1)

	fmt.Println(s, a)
```

这两个例子输出是什么呢？ 会一样吗？ 我们可以先思考下，尝试自己执行看看和理解的是否一致。


在看答案前，我们先巩固下slice截取和追加的一些基本原则。

首先slice的截取，slice一般的截取格式是 `a := s[low:high:max]`，主要包含以下几点：

	1）low表示截取开始的index，包含该index；
	2）high表示截取结束的index，不包含index；
	3）max表示截取后的容量的index，不包含index；
	4）不设置max则max为被截取slice的cap；

因此截取后的`a`，起始index为low，len为high-low，cap为max-low。

而追加的规则就比较简单了，slice追加 `a = append(a, ele)`，主要有两个原则：

	1）slice有剩余空间即cap-len>0，在原slice上追加；
	2）slice无剩余空间，则重新分配底层数组空间，扩容copy后，再进行追加；


有了上面的基本原则，我们就能分析出来上面的结果了。

在例1中，a的起始index为2，len为3-2=1，cap则为5-2=3。因此append时，a中还有容量，在原slice上追加，此时也会改变s的内容。因此输出为：

```
//example 1 output
[1 2 3 1 5] [3 1]  
```

而例2，a的起始index同样为2，len也为3-2=1，但是cap为3-2=1。因此append时，a中没有容量，需要在重新分配底层数据空间，再追加。因此输出为：


```
//example 2 output
[1 2 3 4 5] [3 1]  
```

因此，由于存在多个slice指向同一个底层数据数组的情况，append的时候就需要根据当前slice的cap决定是否影响底层数据数组的内容。

对于slice了解较少的，可以复习下go官网的blog [slices-intro](https://blog.golang.org/slices-intro)。同时，也可以参考前面文章`golang基础-01-array、slice和map`分析下slice截取追加前后底层指针及容量的变化情况。
