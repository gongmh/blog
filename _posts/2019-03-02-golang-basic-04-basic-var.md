---
layout: post
title:  "golang基础-04-基础变量的存储结构"
date:   2019-03-02 14:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}


在内存中，基础变量是若干个连续地址组成的内存空间。指针指向低地址，向高地址延展。并且在计算机的内存中通常使用小端序存储，与网络流中的大端序不同的是，小端序内存低地址空间存储变量低位，内存的高地址空间存储变量的高位。

使用`unsafe`包中的工具，可以对内存进行操作。下面通过`int32`，进行分析基础变量在内存中怎么存储，**[运行一下](https://www.gongmh.com/tools/sharecode?id=YvK86bRMg)**。

``` go
package main

import (
	"fmt"
	"unsafe"
)

func main() {
	var i32Var int32 = 0x12345678

	fmt.Printf("i32Var 值: %x\n", i32Var)
	fmt.Printf("i32Var 内存地址: %p\n", &i32Var)
	fmt.Printf("i32Var 内存中占总字节数:%d\n", unsafe.Sizeof(i32Var))

	//第一个字节
	ptr1 := (*byte)(unsafe.Pointer(&i32Var))
	fmt.Printf("i32Var 第一个字节，地址=%x，值: %x\n", ptr1, *ptr1)

	//第二个字节
	ptr2 := (*byte)(unsafe.Pointer(uintptr(unsafe.Pointer(&i32Var)) + uintptr(1)))
	fmt.Printf("i32Var 第二个字节，地址=%x，值: %x\n", ptr2, *ptr2)

	//第三个字节
	ptr3 := (*byte)(unsafe.Pointer(uintptr(unsafe.Pointer(&i32Var)) + uintptr(2)))
	fmt.Printf("i32Var 第三个字节，地址=%x，值: %x\n", ptr3, *ptr3)

	//第四个字节
	ptr4 := (*byte)(unsafe.Pointer(uintptr(unsafe.Pointer(&i32Var)) + uintptr(3)))
	fmt.Printf("i32Var 第四个字节，地址=%x，值: %x\n", ptr4, *ptr4)
}

```

运行输出结果如下：


```
i32Var 值: 12345678
i32Var 内存地址: 0xc000016050
i32Var 内存中占总字节数:4
i32Var 第一个字节，地址=c000016050，值: 78
i32Var 第二个字节，地址=c000016051，值: 56
i32Var 第三个字节，地址=c000016052，值: 34
i32Var 第四个字节，地址=c000016053，值: 12

```

由此看出，`int32`的变量i32Var占4个字节的内存。变量的首地址是`c000016050`，最后一个字节地址是`c000016053`，也就是说变量内部的地址空间从低到高排布。第一个字节存储的值是`0x78`，对应整个变量值`0x12345678`，说明首字节存储变量的低位值，即小端序。感兴趣的话，可以自行分析其他基础类型~