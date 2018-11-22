---
layout: post
title:  "golang基础-01-array、slice和map"
date:   2018-11-22 21:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}

# 1. array

## 1.1 声明数组

数组具有特定的长度和元素类型类型，如上面intArr是长度为5的元素类型int的数组。

``` golang
var intArr [5]int
fmt.Println(intArr)         //output: [0 0 0 0 0]
```

**数组默认具有零值**，例如int即为0等。

## 1.2 声明并初始化数组

``` golang
intArrNew := [5]int{1, 2, 3, 4, 5}
fmt.Println(intArrNew)         //output: [1 2 3 4 5]
```

## 1.3 获取数组长度

通过golang内置函数`len()`

``` golang
fmt.Println(len(intArr))    //output: 5
```


## 1.4 数组元素存取

通过索引获取或修改数组的值

``` golang
intArr[1] = 100
fmt.Println("set:", a)      //output: set: [0 100 0 0 0]
fmt.Println("get:", a[4])   //output: get: 100
```

## 1.5 多维数组

``` golang
var twoDArr [2][3]int
for i := 0; i < len(twoDArr); i++ {
    for j := 0; j < len(twoDArr[i]); j++ {
        twoDArr[i][j] = i + j
    }
}
fmt.Println(twoDArr)        //output: [[0 1 2] [1 2 3]]
```

# 2. slice

slice在golang中应用更加广泛。

## 2.1 声明slice

声明slice类似声明数组，但是不需要支持元素的数量，如下所示。

``` golang
var intSlice []int
```

## 2.2 声明并初始化slice

``` golang
intSliceNew := []int{1, 2, 3, 4, 5}
```


## 2.3 slice元素存取

新声明的slice没有指向存储空间，直接为slice赋值，会报panic。
需要为slice分配空间，然后使用。

``` golang
//1. 分配空间前
// intSlice[1] = 10            // panic: runtime error: index out of range
fmt.Printf("slice addr: %p, value:%v \n", intSlice, intSlice)
// slice addr: 0x0, value:[]

//2. 分配空间
intSlice = make([]int, 1, 2)
fmt.Printf("slice addr: %p, value:%v \n", intSlice, intSlice)
// slice addr: 0xc0000166a0, value:[0]

//3. 通过index设置
intSlice[0] = 13
fmt.Println(intSlice[0])
fmt.Printf("slice addr: %p, value:%v \n", intSlice, intSlice)
// slice addr: 0xc0000166a0, value:[13]

//4. 未超过容量前，通过append追加
intSlice = append(intSlice, 14)
fmt.Printf("slice addr: %p, value:%v \n", intSlice, intSlice)
// slice addr: 0xc0000166a0, value:[13 14]

//5. 超过容量后，通过append追加
//intSlice[2] = 13            // panic: runtime error: index out of range
intSlice = append(intSlice, 12)
fmt.Printf("slice addr: %p, value:%v \n", intSlice, intSlice)
// slice addr: 0xc000018280, value:[13 14 12]
```


上面我们可以看到slice的存取可以使用类似数组的方法，通过索引来操作。

但是，slice在未申请存储空间前，以及申请存储后越界访问，都会报`panic`。

golang内置的`append`可以对slice做追加，当slice有足够的存储空间是，返回的还是之前的slice，但是当append超过slice的容量时，会生成一个新的slice返回。因此使用append方法追加的时候，需要接收append的返回值。

通过查看slice的地址，可以看到超过容量以后，再给slice追加，slice的地址从`0xc0000166a0`变为了`0xc000018280`。

## 2.4 slice的长度

通过golang内置函数`len()`

``` golang
fmt.Println(len(intSlice))    //output: 3
```

## 2.5 多维slice

``` golang
var twoDSlice [][]int
twoDSlice = make([][]int, 3)
for i := 0; i < len(twoDSlice); i++ {
    twoDSlice[i] = make([]int, 2)
    for j := 0; j < len(twoDSlice[i]); j++ {
        twoDSlice[i][j] = i + j
    }
}
fmt.Println(twoDSlice)        //output: [[0 1] [1 2] [2 3]]
```

# 3. map

## 3.1 map声明

``` golang
var m map[string]int
```

## 3.2 声明并初始化map

``` golang
mNew := map[string]int{
    "key1": 10,
    "key2": 11,
}
```

## 3.3 map的存取

和slice一样，在使用map前需要分配存储空间，否则会报panic。

``` golang
//1. 分配空间前
//m["key1"] = 10       // panic: assignment to entry in nil map

//2. 分配空间
m = make(map[string]int)

//3. 设置值
m["key1"] = 10
m["key2"] = 20
fmt.Println(m, len(m))           // map[key1:10 key2:20] 2

//4. 获取值
value1 := m["key1"]
fmt.Println("value1:", value1)    // value1: 10
fmt.Println(m, len(m))            // map[key1:10 key2:20] 2

_, exist := m["k2"]
fmt.Println("exist:", exist)      // exist: false

//5. 删除值
delete(m, "key2")
fmt.Println(m, len(m))    // map[key1:10] 1
```

获取map对应key的value时，除了返回key对应的value，还会返回一个可选的返回值，表示key是否在map中存在。

当key存在时，可选返回值为true；当key不存在，可选返回值为false，返回的value为对应的零值。

## 3.4 map的长度

``` golang
fmt.Println(len(m))    //output: 1
```