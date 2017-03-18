---
layout: post
title:  "PHP源码分析（1）：变量"
date:  "2017-03-16 13:18:40 -0700"
category: PHP
tags: tools
keywords: PHP
description: ""
---  

我们知道PHP是一种弱类型语言，相对于C、Java等语言来说，弱类型语言的变量能够接收各种类型的值，例如：  
``` php  
$var = 10;
$var = 'this is a string';
$var = array('score' => 100);
$var = new MyClass();
```  
从上面可以看出变量`$var`能接整型、字符串、数组、对象等多种类型的值，非常灵活。
但是PHP是用C来实现的，C语言是强类型的语言，PHP底层是如何实现的呢？  


在`Zend/zend.h`文件中，实际定义一个变量的时候，PHP用的是`zval`来表示变量的，而`zval`又被定义为`_zval_struct`
``` c  
typedef struct _zval_struct zval;
```

下面我们看`_zval_struct`的结构。 
``` c  
struct _zval_struct {
	/* Variable information */
	zvalue_value value;     /* value */
	zend_uint refcount__gc;
	zend_uchar type;    /* active type */
	zend_uchar is_ref__gc;
};
```  
在`_zval_struct`中，PHP除了定义存储变量的`value`，还存在引用计数`refcount__gc`、变量类型`type`、是否是引用`is_ref__gc`。


PHP定义的`type`有以下几种，我们很容易从宏定义的名字看出来每种类型对应的值：
``` c
/* data types */                        
/* All data types <= IS_BOOL have their constructor/destructors skipped */
#define IS_NULL         0                                       
#define IS_LONG         1
#define IS_DOUBLE       2
#define IS_BOOL         3       
#define IS_ARRAY        4       
#define IS_OBJECT       5        
#define IS_STRING       6        
#define IS_RESOURCE     7        
#define IS_CONSTANT     8
#define IS_CONSTANT_ARRAY       9
#define IS_CALLABLE     10
```


`zval`中`value`字段实际保存变量的值，而`value`的类型是`zvalue_value`，结构如下：  
``` c  
typedef union _zvalue_value {
	long lval;                                      /* long value */
	double dval;                            /* double value */
	struct {
		char *val;
		int len;
	} str;
	HashTable *ht;                          /* hash table value */
	zend_object_value obj;  
} zvalue_value;
```  
`zvalue_value`是一个union的结构，相对于struct，union能够节省更多的内存空间，降低PHP的内存占用。另外，从结构中可以看到：`lval`用来保存整数值；`dval`用来保存浮点数值；`str`用来指向字符串的位置并记录长度；`ht`用来存放一个hash表，例如数组等；`obj`则用来存放对象。每个变量的值都存储在这个数据结构中。  

