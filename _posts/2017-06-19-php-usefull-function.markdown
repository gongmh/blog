---
layout: post
title:  "php有用的一些功能函数"
date:  "2017-06-19 07:18:40 +0800"
category: production
tags: php
keywords: php
description: ""
---

* TOC  
{:toc}  

### 数组相关：  
#### 1. 数组获取key(array_keys)  

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

#### 2. 数组获取value(array_values)  

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

#### 3. 两个数组合并(+)  

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

注意：两个数组key相等时，第二个数组不会覆盖第一个数组的value

#### 4. 两个数组合并(array_merge)

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

注意：两个数组key相等时，第二个数组会覆盖第一个数组的value

#### 5. 两个数组合并（array_combine）

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

注意：两个数组的元素个数要一致

#### 6. 取出数组第一个key对应的value(array_shift)

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

#### 7. 取出二维数组指定的列(array_column)

```
$a = array(
  array(
    'id' => 5698,
    'first_name' => 'Bill',
    'last_name' => 'Gates',
  ),
  array(
    'id' => 4767,
    'first_name' => 'Steve',
    'last_name' => 'Jobs',
  ),
  array(
    'id' => 3809,
    'first_name' => 'Mark',
    'last_name' => 'Zuckerberg',
  )
);

print_r(array_column($a, 'last_name'));

// Array
// (
//   [0] => Gates
//   [1] => Jobs
//   [2] => Zuckerberg
// )
```

注意：php版本要5.5+

#### 8. 数组的值进行unique(array_unique)

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

#### 9. 数组替换(array_replace)

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
print_r(array_replace($arr, $newarr));

// Array
// (
//     [a] => first
//     [b] => second
//     [c] => third
//     [d] => 4th
//     [e] => 5th
// )
```

注意：如果一个键存在于第一个数组 array1 同时也存在于第二个数组 array2，第一个数组 array1 中的值将被第二个数组 array2 中的值替换。如果一个键仅存在于第一个数组 array1，它将保持不变。如果一个键存在于第二个数组 array2，但是不存在于第一个数组 array1，则会在第一个数组 array1 中创建这个元素。php版本5.3.0+


#### 10. 数组中所有value之和（array_sum）

```
$arr = array(
    'a' => '1',
    'b' => '2',
    'c' => '3',
    'd' => '4',
);

print_r(array_sum($arr));

// 10
```

#### 11. 用户自定义数组排序(usort)

```
$arr = array(
    'a' => '3',
    'b' => '2',
    'c' => '2',
    'd' => '4',
);
function my_sort($a,$b)
{
    if ($a==$b) return 0;
    return ($a<$b)?-1:1;
}

usort($arr,"my_sort");
print_r($arr);

// Array
// (
//     [0] => 2
//     [1] => 2
//     [2] => 3
//     [3] => 4
// )
```


### 字符串相关  
#### 1. implode
#### 2. explode
#### 3. strstr
#### 4. 

### 其他  

1. array_sum(unpack('C*', $metric)