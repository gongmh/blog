---
layout: post
title:  "php有用的一些功能函数"
date:  "2017-06-20 07:18:40 +0800"
category: production
tags: php
keywords: php
description: ""
---

* TOC  
{:toc}  

### 数组相关：  
1. 数组获取key(array_keys)


```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
);

print_r(array_keys($arr));

// Array
// (
//     [0] => a
//     [1] => b
//     [2] => c
//     [3] => d
// )
```

2. 数组获取value(array_values)

```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
);

print_r(array_values($arr));

// Array
// (
//     [0] => first
//     [1] => second
//     [2] => third
//     [3] => fourth
// )
```

3. 两个数组合并(+)

```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
);

$newarr = array(
    'd' => '4th',
    'e' => '5th',
);

print_r($arr + $newarr);

// Array
// (
//     [a] => first
//     [b] => second
//     [c] => third
//     [d] => fourth
//     [e] => 5th
// )
```
4. 两个数组合并(array_merge)
```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
);

$newarr = array(
    'd' => '4th',
    'e' => '5th',
);

print_r(array_merge($arr, $newarr));

// Array
// (
//     [a] => first
//     [b] => second
//     [c] => third
//     [d] => 4th
//     [e] => 5th
// )
```
5. 两个数组合并（array_combine）
```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    // 'c' => 'third',
    // 'd' => 'fourth',
);

$newarr = array(
    'd' => '4th',
    'e' => '5th',
);

print_r(array_combine($arr, $newarr));

// Array
// (
//     [first] => 4th
//     [second] => 5th
// )
```
6. 取出数组第一个key对应的value(array_shift)
```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
);

print_r(array_shift($arr));

// first
```
7. 取出二维数组指定的列(array_column)

8. 数组的值进行unique(array_unique)
```
$arr = array(
    'a' => 'first',
    'b' => 'second',
    'c' => 'third',
    'd' => 'fourth',
    'e' => 'fourth',
);

print_r(array_unique($arr));

// Array
// (
//     [a] => first
//     [b] => second
//     [c] => third
//     [d] => fourth
// )
```
9. (array_replace)
10. array_sum(unpack('C*', $metric));
11. (usort)


### 字符串相关  
1. implode
2. explode
3. strstr
4. 

### 其他  