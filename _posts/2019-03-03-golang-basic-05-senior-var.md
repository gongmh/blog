---
layout: post
title:  "golang基础-05-数组&切片&string变量的存储结构"
date:   2019-03-03 18:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}

上文分析了，单个基础变量的内存存储结构，下面我们分析下基础变量组合形式的变量。

# 1. 数组(array)

对`int32`的数组进行分析，**[运行一下](https://www.gongmh.com/tools/s?id=vxoWk-RGR)**。

``` go
package main

import (
	"fmt"
	"unsafe"
)

func main() {
	var arrVar = [...]int32{
		'b', 2, 3, 4,
	}

	fmt.Printf("arrVar 值: %d\n", arrVar)
	fmt.Printf("arrVar 内存地址: %p\n", &arrVar)
	fmt.Printf("arrVar 内存中占总字节数:%d\n", unsafe.Sizeof(arrVar))
	fmt.Printf("arrVar 元素个数:%d\n", len(arrVar))

	//第一个元素
	ptr1 := (*int32)(unsafe.Pointer(&arrVar))
	fmt.Printf("arrVar 第一个元素，地址=%x，值: %d\n", ptr1, *ptr1)

	//第二个元素
	ptr2 := (*int32)(unsafe.Pointer(uintptr(unsafe.Pointer(&arrVar)) + uintptr(4*1)))
	fmt.Printf("arrVar 第二个元素，地址=%x，值: %d\n", ptr2, *ptr2)

	//第三个元素
	ptr3 := (*int32)(unsafe.Pointer(uintptr(unsafe.Pointer(&arrVar)) + uintptr(4*2)))
	fmt.Printf("arrVar 第三个元素，地址=%x，值: %d\n", ptr3, *ptr3)

	//第四个元素
	ptr4 := (*int32)(unsafe.Pointer(uintptr(unsafe.Pointer(&arrVar)) + uintptr(4*3)))
	fmt.Printf("arrVar 第四个元素，地址=%x，值: %d\n", ptr4, *ptr4)
}

```

输出结果如下：

```
arrVar 值: [98 2 3 4]
arrVar 内存地址: 0xc000016050
arrVar 内存中占总字节数:16
arrVar 元素个数:4
arrVar 第一个元素，地址=c000016050，值: 98
arrVar 第二个元素，地址=c000016054，值: 2
arrVar 第三个元素，地址=c000016058，值: 3
arrVar 第四个元素，地址=c00001605c，值: 4
```


结果可以看出，数组的元素类型是`int32`，每个占用4个字节，一共4个元素，因此数组总共占用16字节。数组的首地址是`c000016050`对应数组第一个元素，最后一个元素的首地址是`c00001605c`，可以得出数组元素连续分配，并且地址从低到高排布。

# 2. 切片(slice)

仍然使用`int32`类型，对切片进行分析，**[运行一下](https://www.gongmh.com/tools/s?id=hT-YkaRMR)**。

``` go

package main

import (
	"fmt"
	"unsafe"
)

func main() {
	var arrVar = []int32{
		'b', 2, 3, 4,
	}

	fmt.Printf("arrVar 值: %d\n", arrVar)
	fmt.Printf("arrVar 内存地址: %p\n", &arrVar)
	fmt.Printf("arrVar 内存中占总字节数:%d\n", unsafe.Sizeof(arrVar))
	fmt.Printf("arrVar 元素个数:%d\n", len(arrVar))
}
```

输出结果如下：

```
arrVar 值: [98 2 3 4]
arrVar 内存地址: 0xc00009c020
arrVar 内存中占总字节数:24
arrVar 元素个数:4
```

可以看出，切片4个元素，但是切片占用的总字节数为24字节。可以自行试下，无论切片多少元素，slice变量本身占用的字节数不变，都是24字节。

分析golang源码（go1.11）在文件`src/runtime/slice.go`中可以看到如下结构：

``` go
type slice struct {
	array unsafe.Pointer
	len   int
	cap   int
}
```

说明slice实际上是由一个指针和两个int变量组成，指针和int都是占8个字节，也就对应了上面的slice总共占用24字节。继续用`slice结构体`对切片进行分析，**[运行一下](https://www.gongmh.com/tools/s?id=nBvFZaRGR)**。

``` go
package main

import (
	"fmt"
	"unsafe"
)

func main() {
	var sliceVar = []int32{
		'b', 2, 3, 4,
	}

	fmt.Printf("sliceVar 值: %d\n", sliceVar)

	type sliceStruct struct {
		array unsafe.Pointer
		len   int
		cap   int
	}

	//将切片转换为sliceStruct结构
	ptr := (*sliceStruct)(unsafe.Pointer(&sliceVar))
	fmt.Printf("sliceVar 地址=%p，值: %#v\n", &sliceVar, *ptr)

	//取出sliceStruct中指针，再找指针值对应地址内存的值
	arrPtr := (*[4]int32)(ptr.array)
	fmt.Printf("sliceVar 中指针指向的值: %#v\n", *arrPtr)
}

```

执行结果如下：

```
sliceVar 值: [98 2 3 4]
sliceVar 地址=0xc00000c060，值: main.sliceStruct{array:(unsafe.Pointer)(0xc000016050), len:4, cap:4}
sliceVar 中指针指向的值: [4]int32{98, 2, 3, 4}
```

切片对应的结构体中，元素`array`指向的是切片元素存储的数组地址，元素`len`和`cap`分别对应切片的长度和容量。也对应之前知道的知识，切片底层是数组，一个数组可以被多个切片引用，感兴趣的可以自己试验下。

# 3. 字符串(string)

字符串的结构类似切片，不过字符串是不可被改变的，也就只有指针和长度。**[运行一下](https://www.gongmh.com/tools/s?id=XUFrZ-RMg)**。


```
package main

import (
	"fmt"
	"unsafe"
)

func main() {
	var stringVar string = "hi"

	fmt.Printf("sliceVar 值: %s\n", stringVar)
	fmt.Printf("sliceVar 内存中占总字节数:%d\n", unsafe.Sizeof(stringVar))

	type stringStruct struct {
		array unsafe.Pointer
		len   int
	}

	//将切片转换为sliceStruct结构
	ptr := (*stringStruct)(unsafe.Pointer(&stringVar))
	fmt.Printf("sliceVar 地址=%p，值: %#v\n", &stringVar, *ptr)

	//取出sliceStruct中指针，再找指针值对应地址内存的值
	arrPtr := (*[5]byte)(ptr.array)
	fmt.Printf("sliceVar 中指针指向的值: %c, %c\n", (*arrPtr)[0], (*arrPtr)[1])
}

```

执行结果如下：

```
sliceVar 值: hi
sliceVar 内存中占总字节数:16
sliceVar 地址=0xc000010210，值: main.stringStruct{array:(unsafe.Pointer)(0x4c1b71), len:2}
sliceVar 中指针指向的值: h, i
```

字符串包含指针和int两个元素，固定占用16个字节。其中指针指向一个byte的数组，int元素代表的是字符串的长度。


# 4. 总结

从以上可以看出，数组是一段连续的内存空间。而切片和字符串本身是一个指针，指针再指向具体的内容的数组。
