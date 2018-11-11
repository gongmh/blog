---
layout: post
title:  "Redis源码分析(002)--基础数据结构"
date:  "2017-08-19 00:18:40 +0800"
category: redis
tags: redis
keywords: redis
description: ""
---

* TOC  
{:toc} 

### 1. 概述

本文学习总结redis底层基础的数据结构，参考的redis版本为`3.2.9`。

### 2. sds

2.1 特点及应用

sds（简单动态字符串，simple dynamic string）是二进制安全的字符串，能够以O(1)的时间复杂度获取字符串的长度。并且能够避免缓存区溢出，操作前判断sds长度，不足则自动扩展。

2.2 数据结构

sds的数据结构就是普通的字符串，如下所示：

``` c
 typedef char *sds;
```
实际上，根据字符串长度的不同，redis定义了多个结构sds：`sdshdr5`（未使用）、`sdshdr8`、`sdshdr16`、`sdshdr32`、`sdshdr64`，每种结构所对应的sds head不同。以`sdshdr8`为例，结构如下：

``` c
struct __attribute__ ((__packed__)) sdshdr8 {
    uint8_t len; /* used */
    uint8_t alloc; /* excluding the header and null terminator */
    unsigned char flags; /* 3 lsb of type, 5 unused bits */
    char buf[];
};
```

	注：结构体参数`__attribute__ ((__packed__))`说明结构体取消内存对齐优化，按照实际占用字节数进行对齐。

* `len` 记录sds结构总字节数；
* `alloc` 字符数组的长度（不包括头部和'\0'结束符）；
* `flags` 低3bit用来保存sds的类型，`SDS_TYPE_5`（0）, `SDS_TYPE_8`（1）, `SDS_TYPE_16`（2）, `SDS_TYPE_32`（3）, `SDS_TYPE_64`（4）;
* `buf` 记录实际字符串的值。

我们以`sdshdr8`为例，如图介绍sds在内存中的结构：

[<img src="{{site.baseurl}}/assets/redis_basic_data_struct/redis-sds.png" style="width:1000px" alt="sds" />]( /blog/assets/redis_basic_data_struct/redis-sds.png )

另外实际上每个sds指针指向buf，因此sds也能像普通字符串一样处理。

sds能够根据字符串的长度自动改变类型。例如当字符串长度小于256时，type为`SDS_TYPE_8`，当字符串长度大于255且小于65536时，会自动扩展到`SDS_TYPE_16`。具体判断如下函数所示：

``` c
static inline char sdsReqType(size_t string_size) {
    if (string_size < 1<<5)
        return SDS_TYPE_5;
    if (string_size < 1<<8)			//字符长度小于256
        return SDS_TYPE_8;
    if (string_size < 1<<16)		//字符长度小于65536
        return SDS_TYPE_16;
    if (string_size < 1ll<<32)		//字符长度小于4294967296
        return SDS_TYPE_32;
    return SDS_TYPE_64;
}
```

2.3 知识点

``` c
#define SDS_HDR_VAR(T,s) struct sdshdr##T *sh = (void*)((s)-(sizeof(struct sdshdr##T)));

//注意无分号
#define SDS_HDR(T,s) ((struct sdshdr##T *)((s)-(sizeof(struct sdshdr##T))))
```

	在c语言中，define中的'##'表示连接的意思。例如SDS_HDR_VAR(8,s)

`SDS_HDR_VAR(T,s)`表示根据s，定义指针sh，并初始化指向实际sds的起始地址，用法：SDS_HDR_VAR(16,s);

`SDS_HDR(T,s)`表示一个指向实际sds的起始地址的指针，用法：SDS_HDR(32,s)->len;

2.4 重要api

仅列出重要的api函数，部分省略。

``` c
//根据长度创建相应类型的sds，并初始化sds（若init不为空）
sds sdsnewlen(const void *init, size_t initlen);

//释放一个sds
void sdsfree(sds s);

//s后拼接长度为len的t字符串
sds sdscatlen(sds s, const void *t, size_t len);

//从t中复制长度为len的二进制安全的字符串到s中
sds sdscpylen(sds s, const char *t, size_t len);

//使用类似printf的格式在s后拼接字符串，基于sprintf() family functions，较慢
sds sdscatvprintf(sds s, const char *fmt, va_list ap);

//使用类似printf的格式在s后拼接字符串，自己实现，较快。但是实现的格式是printf-alike的子集
sds sdscatfmt(sds s, char const *fmt, ...);

//移除s两端在cset中的字符
sds sdstrim(sds s, const char *cset);

//获取s的子集
void sdsrange(sds s, int start, int end);

//比较两个sds
int sdscmp(const sds s1, const sds s2);

//将s根据sep进行切割，返回一个sds数组，count为数组元素个数
sds *sdssplitlen(const char *s, int len, const char *sep, int seplen, int *count);

//为s拼接字符，非打印字符会被转换为"\x<hex-number>"
sds sdscatrepr(sds s, const char *p, size_t len);

//将line转换为参数sds数组，argc为数组中元素个数
sds *sdssplitargs(const char *line, int *argc);

//将s中from的元素替换成对应的to元素
sds sdsmapchars(sds s, const char *from, const char *to, size_t setlen);

//将argv中的每个元素（除了最后一个）连接上sep，连接成一个sds
sds sdsjoin(char **argv, int argc, char *sep);

/* Low level functions exposed to the user API */
//增加s的空闲区域，可以让随后的操作在s后增加addlen字节。
//注意，该函数不会改变s的len。
sds sdsMakeRoomFor(sds s, size_t addlen);

//增加/减少sds的长度
void sdsIncrLen(sds s, int incr);

//重新分配sds，以便去除多余的字节
sds sdsRemoveFreeSpace(sds s);

//返回s占用总的字节数，即sds头长度+字符串长度+空余长度+结束（sdsHeader + string + free + 1）
size_t sdsAllocSize(sds s);

//根据s获取sds的真实起始地址
void *sdsAllocPtr(sds s);

```


<!-- ### 3. robj

### 4. dict

### 5. zip list

### 6. skip list -->