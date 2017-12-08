---
layout: post
title:  "Go类型断言"
date:  "2017-12-08 13:52:17 +800"
category: go
tags: go assertions
keywords: go assertions
description: ""
---

* TOC  
{:toc}
Golang中类型断言主要体现两种方式：`Type assertions`和`Type switches`，下面我们分别进行分析。

### Type assertions
对于==接口类型==的表达式x和类型T，Go的[类型断言](https://golang.org/ref/spec#Type_assertions)可表示如下，

```go
x.(T)
```

`类型断言`将断言变量x不为`nil`并且x中值的类型是`T`。如果`T`不是接口类型，`x.(T)`断言x的动态类型与T相同；如果`T`是接口类型，`x.(T)`断言x的动态类型实现了接口`T`。

如果断言成立，表达式的值是存储在x中的值，并且其类型为T；如果断言失败，则会发生运行时panic。

```go
var x interface{} = 7   //x动态类型是int，值为7
i := x.(int)            //i的类型是int，值为7
j := x.(float64)        //panic
```

类型断言在赋值或初始化的时候，会产生一个额外的bool值，即如果断言成功ok为true。否则是false，并且v的值是类型T的零值，此时不会发生panic。

```go
v, ok := x.(T)
```

即，如下示例

```go
    var value interface{}
    value = "string"
    if str, ok := value.(string); ok {
        fmt.Printf("%T  %v", str, str)
    }
    if i, ok := value.(int); ok {
        fmt.Printf("%T  %v", i, i)
    }
```


### Type switches

[Type switches](https://golang.org/ref/spec#Switch_statements)比较的是变量的类型而不是值
，使用保留字`type`作为断言的参数。即，

```go
switch newval := value.(type) {
case int :
    //do something
default :
    //do something
}
```

需要注意value是interface，变量newval类型是转换后的类型。下面我们看一下具体的例子，

```go
package main

import "fmt"

type mystruct struct {
    string
}

func main() {
    //v := 3.12     //case float
    //var v mystruct    //case mystruct
    var v interface{}   //case nil

    checkType(v)

    fmt.Printf("\n%v  %T", v, v)
}

func checkType(v interface{}) {
    //switch v.(type) {
    switch t := v.(type) {
    case *int:
        *t = 10 //change value
        fmt.Println("\rIn *int")
    case float64:
        t = 20 //not change value
        fmt.Println("\rIn float64")
    case mystruct:
        fmt.Println("\rIn mystruct")
    case nil:
        fmt.Println("\rIn nil")
    default:
        fmt.Println("\rIn default")
    }
}
```

### 总结

使用`x.(typename)`时需要注意以下问题，

1. **x必须是`interface{}`**
2. `typename`为具体类型的时候，必须进行`comma, ok`判断，否则容易产生panic
3. `typename`是`type`时，表达式只能用在switch中，另外对x的数据做操作的时候，需新定义变量t， 即`t := v.(type)`

