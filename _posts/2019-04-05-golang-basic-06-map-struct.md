---
layout: post
title:  "golang基础-06-map的存储结构"
date:   2019-04-05 13:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}

在开发过程中，经常通过map实现kv数据的处理。源码`src/runtime/map.go`中可以看到map的底层结构，网上也有很多对map的分析，此处不再赘述。本文先综述map的结构，然后根据具体的一个map变量分析map底层是怎么存储的。


# 1. 内存中的map的结构

<img src="{{site.baseurl}}/assets/golang_map/map_mem_struct.png" style="width:700px" alt="map_mem_struct" />

map的实现是通过hash来实现，底层是一个hmap和若干个bmap组成，基本结构见上图。

总结下来就是以下几点：

	1. map变量是一个指针，指向hmap结构体；
	2. hmap中包含map中元素的个数count，桶的个数(2^B)，桶数组的地址`buckets`等，
	3. hmap的元素`buckets`指向bmap的数组，根据hash确定key落入哪个bmap中；
	4. hmap的元素`oldbuckets`指向扩容阶段之前的bmap的数组；
	5. bmap中tophash用来快速定位key是否在这个bmap中；
	6. bmap中8个kv槽位用来存储key和value；
	7. bmap中overflow用来拉链指向新的桶，解决添加超过key时，超过8个槽位的情况；



# 2. 实例分析

接下来通过一个具体的例子，分析下map变量的内存结构，感兴趣的可以自己[试下](https://gongmh.com/tools/s?id=CFDpa_RMR)。

<img src="{{site.baseurl}}/assets/golang_map/map_var_eg.png" style="width:700px" alt="map_var_eg" />

图中列出变量的实际地址，以及每个字段的值，接下来分步解析。

### 2.1 定义map变量

``` go
	mapLen := 9
	var mapVar = make(map[int]int, mapLen)
	for i := 0; i < mapLen; i++ {
		mapVar[i] = i + 1
	}
```
首先，定义一个`map[int]int`的变量`mapVar`，由于map的一个桶中只有8个槽位，此例中设置map中有9对kv。

### 2.2 变量自身结构

``` go
	fmt.Printf("mapVar 内容：%v\n", mapVar)
	//output: mapVar 内容：map[0:1 1:2 2:3 3:4 4:5 5:6 6:7 7:8 8:9]

	fmt.Printf("mapVar 占用字节数：%v\n", unsafe.Sizeof(mapVar))
	//output: mapVar 占用字节数：8

	mapVarValue := uintptr(*(*int)(unsafe.Pointer(&mapVar)))
	fmt.Printf("mapVar 地址:%p，值:%x\n", &mapVar, mapVarValue)
	//output: mapVar 地址:0xc00000e028，值:c000054150
```

变量`mapVar`是一个指针，指向hmap结构体。`mapVar`变量本身占用字节数为8，所在的地址为`0xc00000e028`，存储的值为`c000054150`。
变量`mapVar`对应的map为`map[0:1 1:2 2:3 3:4 4:5 5:6 6:7 7:8 8:9]`。

### 2.3 hmap结构

接下来继续分析hmap每个字段的内存结构。

``` go
	//hmap 结构体首地址
	hmapBaseAddr := mapVarValue
```

hmap结构体的首地址就是`mapVar`的值，获取到hamp首地址`hmapBaseAddr`。

``` go
	//count address:  base
	countPtr := (*int)(unsafe.Pointer(hmapBaseAddr))
	fmt.Printf("hmap count 地址:%p，值:%d\n", countPtr, *countPtr)
	//output: hmap count 地址:0xc000054150，值:9
```

hmap的`count`字段存储map中包含的元素个数，本例中就是9。

``` go
	//hmap B address:  base + count(int,8) + flags(uint8,1)
	bPtr := (*uint8)(unsafe.Pointer(hmapBaseAddr + 9))
	fmt.Printf("hmap B 地址:%p，值:%d\n", bPtr, *bPtr)
	//output: hmap B 地址:0xc000054159，值:1
```

hmap的`B`字段值为1，表明mapVar中一个有2(`2^B`)个桶(bmap)。

``` go
	//hmap buckets address:  base + count(int,8) + flags(uint8,1) + B(uint,1) + noverflow(uint16,2) + hash(uint32,4)
	bucketsPtr := (*int)(unsafe.Pointer(hmapBaseAddr + 16))
	fmt.Printf("hmap buckets 地址:%p，值:%x\n", bucketsPtr, *bucketsPtr)
	//output: hmap buckets 地址:0xc000054160，值:c000070000
```

hmap的`buckets`字段是一个指针，指向bmap结构体数组，也就是[2]bmap。

``` go
	//hmap oldbuckets address:  base + count(int,8) + flags(uint8,1) + B(uint,1) + noverflow(uint16,2) + hash(uint32,4) + bucktes(unsafe.Pointer,8)
	oldbuckets := (*int)(unsafe.Pointer(hmapBaseAddr + 24))
	fmt.Printf("hmap oldbuckets 地址:%p，值:%x\n", oldbuckets, *oldbuckets)
	//output: hmap oldbuckets 地址:0xc000054168，值:0
```

hmap的`oldbuckets`字段同样也是一个指针，指向bmap结构体数组，存储map扩缩容时的桶数组指针。此时没有扩缩容过程，oldbuckets的值为0。

### 2.4 bmap数组分析

找到bmap数组的首地址，即hmap字段`buckets`的值`c000070000`。

``` go
	//hmap buckets 指向的bmap数组首地址
	bmapArrPtr := uintptr(*(*int)(unsafe.Pointer(bucketsPtr)))

	//bmap数组第一个bmap结构体首地址
	firstBucketPtr := bmapArrPtr
```

得到bmap数组的第一个结构体bmap[0]的指针。


``` go
	//bmap[0] tophash
	tophash0 := (*[8]uint8)(unsafe.Pointer(firstBucketPtr))
	fmt.Printf("bmap[0] tophash 地址:%p，值:%d\n", tophash0, *tophash0)
	//output: bmap[0] tophash 地址:0xc000070000，值:[26 125 177 0 0 0 0 0]
```

bmap[0]的`tophash`记录，对应key的hash值高位以及key的状态标识。可以看到桶(bmap[0])的前三个key有值，即第一个桶存放3个kv。

``` go
	//bmap[0] keys
	keys0 := (*[8]int)(unsafe.Pointer(firstBucketPtr + 8))
	fmt.Printf("bmap[0] keys 地址:%p，值:%d\n", keys0, *keys0)
	//output: bmap[0] keys 地址:0xc000070008，值:[1 4 6 0 0 0 0 0]

	//bmap[0] values
	values0 := (*[8]int)(unsafe.Pointer(firstBucketPtr + 8 + 8*8))
	fmt.Printf("bmap[0] values 地址:%p，值:%d\n", values0, *values0)
	//output: bmap[0] values 地址:0xc000070048，值:[2 5 7 0 0 0 0 0]

	//bmap[0] overflow
	overflow0 := (*int)(unsafe.Pointer(firstBucketPtr + 8 + 8*8 + 8*8))
	fmt.Printf("bmap[0] overflow 地址:%p，值:%d\n", overflow0, *overflow0)
	//output: bmap[0] overflow 地址:0xc000070088，值:0
```

bmap[0]中继续往下找，找到8个keys和8个values。看到map的3对kv值存储在第一个桶中。overflow的值为0，说明没有碰撞的key被拉链出去。

``` go
	//bmap数组第二个bmap结构体首地址
	secondBucketPtr := bmapArrPtr + 8 + 8*8 + 8*8 + 8
```

继续往下找，找到bmap数组的第二个结构体bmap[1]的指针。

``` go
	//bmap[1] tophash
	tophash := (*[8]uint8)(unsafe.Pointer(secondBucketPtr))
	fmt.Printf("bmap[1] tophash 地址:%p，值:%d\n", tophash, *tophash)
	//output: bmap[1] tophash 地址:0xc000070090，值:[88 156 97 100 191 59 0 0]
```

同样，bmap[1]可以看出第二个桶存放6对map的kv。


``` go
	//bmap[1] keys
	keys := (*[8]int)(unsafe.Pointer(secondBucketPtr + 8))
	fmt.Printf("bmap[1] keys 地址:%p，值:%d\n", keys, *keys)
	//output: bmap[1] keys 地址:0xc000070098，值:[0 2 3 5 7 8 0 0]

	//bmap[1] values
	values := (*[8]int)(unsafe.Pointer(secondBucketPtr + 8 + 8*8))
	fmt.Printf("bmap[1] values 地址:%p，值:%d\n", values, *values)
	//output: bmap[1] values 地址:0xc0000700d8，值:[1 3 4 6 8 9 0 0]

	//bmap[1] overflow
	overflow := (*int)(unsafe.Pointer(secondBucketPtr + 8 + 8*8 + 8*8))
	fmt.Printf("bmap[1] overflow 地址:%p，值:%d\n", overflow, *overflow)
	//output: bmap[1] overflow 地址:0xc000070118，值:0
```

bmap[1]中，找到8个keys和8个values。看到map的6对kv值存储在第二个桶中。overflow的值为0，说明没有碰撞的key被拉链出去。

至此，mapVar变量的内存分析完毕，详细内容可以参考本节的图及[本例源码](https://gongmh.com/tools/s?id=CFDpa_RMR)，感兴趣的同学可以继续分析map其他情况~

注：本文基于go1.11版本分析，其他版本请基于对应版本的源码分析。

