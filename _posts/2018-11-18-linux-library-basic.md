---
layout: post
title:  "linux中library -- 基础知识"
date:   2018-11-18 21:40:18 +0800
categories: linux
tags: linux lib dependence gcc
author: gongmh
---

* TOC
{:toc}

# 1. 引言

> In computer science, a library is a collection of non-volatile resources used by computer programs, often for software development. These may include configuration data, documentation, help data, message templates, pre-written code and subroutines, classes, values or type specifications.


在计算机科学中，库（library）是用于开发软件的子程序集合。库和可执行文件的区别是，库不是独立程序，他们是向其他程序提供服务的代码。

# 2. 基础


程序库的引入使程序更加模块化化，编译更快，而且更容易更新。

## 2.1 linux中库的分类

程序库可以分为三种：静态库（static libraries）和共享库（shanred libraries）。

静态库是程序在编译时，将库内容加入到可执行程序中，在linux中静态库通常以`.a`结尾。共享库是可以被多个程序共享使用，而不是在生成程序的时候被链接器拷贝到可执行程序中，共享库在linux中以`.so`结尾。

而另外存在的动态加载库（dynamically loaded libraries）在程序执行时，根据需要动态加载共享库。


## 2.2 库文件在文件系统中的路径

大多数系统都倾向于遵守[GNU的标准](https://www.gnu.org/prep/standards/html_node/Directory-Variables.html)，即默认放在`/usr/local/lib`目录下。FHS（Filesystem Hierarchy Standard）[推荐](http://www.pathname.com/fhs/pub/fhs-2.3.html)，大多数的库文件应该放入`/usr/lib`目录下，系统启动需要的库文件放在`/lib`目录下，非系统库文件的放在`/usr/local/lib`下。[其实二者并不冲突，GNU推荐是的开发者的源码，而FHS推荐的是发布者，发布者可以通过系统的包管理工具选择性地覆盖源码](http://tldp.org/HOWTO/Program-Library-HOWTO/shared-libraries.html)。注意，如果我们的库文件只能通过其他库文件调用，我们的库文件应该放在`/usr/local/libexec`或`/usr/libexec`。

特别需要注意的是，基于Red Hat的系统并没有将`/usr/local/lib`目录引入到默认的库搜索路径下，因此我们的centos也会出现这样的问题。可以在`/etc/ld.so.conf`加入上面的路径，也可以通过设置环境变量`LD_LIBRARY_PATH`来解决。

以centos为例，库文件一般在以下几个地方存在，如果是64位系统，下面路径可能还会存在`*/lib64/`。

另外，在linux中`usr`并不是user的意思，而是`unix system resrouces`的缩写，本人已经被误导了多年。

```
/lib/
/usr/lib/
/usr/local/lib/
# /var/lib/
```

## 2.3 库文件的版本

表示、文件名、版本、elf
共享库以lib为前缀，例如`libhello.so.x.y.z`表示共享库`hello`。后面`x.y.z`是版本号，x是主版本号(Major Version Number)，y是次版本号(Minor Version Number)，z是发布版本号(Release Version Number)。

主版本号(不兼容)：重大升级，不同主版本的库之间的库是不兼容的。所以如果要保证向后兼容就不能删除旧的动态库的版本。

次版本号(向下兼容): 增量升级，增加一些新的接口但保留原有接口。高次版本号的库向后兼容低次版本号的库。

发布版本号(相互兼容)：库的一些诸如错误修改、性能改进等，不添加新接口，也不更改接口。主版本号和次版本号相同的前提下，不同发布版本之间完全兼容。

Linux采用SO-NAME( Shortfor shared object name)的命名机制来记录共享库的依赖关系。每个共享库都有一个对应的“SO-NAME”(共享库文件名去掉次版本号和发布版本号)。比如共享库名为libhello.so.3.8.2,那么它的SO-NAME就是libhello.so.3。

在Linux系统中，系统会为每个共享库所在的目录创建一个跟SO-NAME相同的并且指向它的软连接(Symbol Link)。这个软连接会指向目录中主版本号相同、次版本号和发布版本号最新的共享库。也就是说，比如目录中有两个共享库版本分别为：/lib/libtest.so.3.8.2和/lib/libtest.so.3.7.5，那么软连接/lib/libtest.so.3指向/lib/libtest.so.3.8.2。

建立以SO-NAME为名字的软连接的目的是，使得所有依赖某个共享库的模块，在编译、链接和运行时，都使用共享库的SO-NAME，而不需要使用详细版本号。在编译生产ELF文件时候，如果文件A依赖于文件B，那么A的链接文件中的”.dynamic”段中会有DT_NEED类型的字段，字段的值就是B的SO-NAME。这样当动态链接器进行共享库依赖文件查找时，就会依据系统中各种共享库目录中的SO-NAME软连接自动定向到最新兼容版本的共享库。

当我们在编译器里使用共享库的时候，如用GCC的“-l”参数链接共享库libtXXX.so.3.8.1，只需要在编译器命令行指定 -l XXX 即可，省略了前缀和版本信息。编译器会根据当前环境，在系统中的相关路径(往往由-L参数指定)查找最新版本的XXX库。这个XXX就是共享库的“链接名”。不同类型的库可能有相同的链接名，比如C语言运行库有静态版本(libc.a)也动态版本(libc.so.x.y.z)的区别，如果在链接时使用参数”-lc”,那么连接器就会根据输出文件的情况(动态/静态)来选择合适版本的库。eg. ld使用“-static”参数时吗，”-lc”会查找libc.a;如果使用“-Bdynamic”(默认),会查找最新版本的libc.so.x.y.z。

## 2.4 常见的库文件

`libc` 是 Linux 下的 ANSI C 函数库。

[`glibc`](http://www.gnu.org/software/libc/libc.html) 是 Linux 下的 GUN C 函数库。

`libc++`是针对clang编译器特别重写的C++标准库，那`libstdc++`自然就是gcc的事儿了。clang与libc++的关系就像libstdc++与gcc。

再说说libstdc++，glibc的关系。 libstdc++与gcc是捆绑在一起的，也就是说安装gcc的时候会把libstdc++装上。 那为什么glibc和gcc没有捆绑在一起呢？
相比glibc，libstdc++虽然提供了c++程序的标准库，但它并不与内核打交道。对于系统级别的事件，libstdc++首先是会与glibc交互，才能和内核通信。相比glibc来说，libstdc++就显得没那么基础了。（本段内容待确认）

## 2.5 常用常量

LD_LIBRARY_PATH

LD_PRELOAD

LD_DEBUG

## 2.6 一些相关工具

### 2.6.1 [readelf](https://linux.die.net/man/1/readelf)

ELF(Executable and Linking Format)定义了目标文件内部信息如何组成和组织的文件格式。内核会根据这些信息加载可执行文件，根据该文件可以知道从文件哪里获取代码，从哪里获取初始化数据，在哪里应该加载共享库等信息。

readelf就是linux下展示elf文件内容的命令。本文中使用`-d --dynamic           Display the dynamic section (if present)`参数展示库的使用情况。

```
$ readelf -d demo_share

Dynamic section at offset 0x798 contains 21 entries:
  Tag        Type                         Name/Value
 0x0000000000000001 (NEEDED)             Shared library: [libhello.so.0]
 0x0000000000000001 (NEEDED)             Shared library: [libc.so.6]
 0x000000000000000c (INIT)               0x400488
 ...
```

其中标记为NEEDED的就是依赖的库。

### 2.6.2 [ldd](http://man7.org/linux/man-pages/man1/ldd.1.html)

> ldd - print shared object dependencies.

同样，ldd命令也是用来查看程序的依赖。

```
$ ldd demo_share
	linux-vdso.so.1 =>  (0x00007ffc18fea000)
	libhello.so.0 => ./libhello.so.0 (0x00007f490b5b1000)
	libc.so.6 => /lib64/libc.so.6 (0x00007f490b21d000)
	/lib64/ld-linux-x86-64.so.2 (0x00007f490b7b2000)
```

ldd同样也能看到程序依赖的库文件。

### 2.6.3 [ldconfig](http://man7.org/linux/man-pages/man8/ldconfig.8.html)

> ldconfig - configure dynamic linker run-time bindings.

ldconfig是用来配置**运行时**动态链接的绑定。

### 2.6.4 [strace](http://man7.org/linux/man-pages/man1/strace.1.html)

> strace - trace system calls and signals

这个后续会专门总结。

### 2.6.5 [nm](http://man7.org/linux/man-pages/man1/nm.1.html)

> nm - list symbols from object files

> The nm(1) command can report the list of symbols in a given library. It works on both static and shared libraries. For a given library nm(1) can list the symbol names defined, each symbol's value, and the symbol's type. It can also identify where the symbol was defined in the source code (by filename and line number), if that information is available in the library (see the -l option).

# 3. 自定义库示例

有了上面的理解和基础，我们实际去，例子来自[tldp](http://tldp.org/HOWTO/Program-Library-HOWTO/more-examples.html)

首先是文件`libhello.c` 和 `libhello.h`

``` c
/* libhello.c - demonstrate library use. */

#include <stdio.h>

void hello(void) {
  printf("Hello, library world.\n");
}
```

``` c
/* libhello.h - demonstrate library use. */

void hello(void);
```

然后是调用库文件的程序`demo.c`

``` c
/* demo.c -- demonstrate direct use of the "hello" routine */

#include "libhello.h"

int main(void) {
 hello();
 return 0;
}
```

## 3.1 编写并使用静态库

编译打包生成静态库文件

```
$ gcc -Wall -g -c -o libhello-static.o libhello.c
$ ar rcs libhello-static.a libhello-static.o
```

使用静态库

```
$ gcc -Wall -g -c demo.c -o demo.o
$ gcc -g -o demo_static demo.o -L. -lhello-static
$ ./demo_static
Hello, library world.
```

## 3.2 编写并使用共享库

编译生成共享库文件

```
$ gcc -fPIC -Wall -g -c libhello.c
$ gcc -g -shared -Wl,-soname,libhello.so.0 -o libhello.so.0.0 libhello.o -lc
```

安装、链接共享库
```
$ /sbin/ldconfig -n .
$ ln -sf libhello.so.0 libhello.so
```

使用共享库
```
$ gcc -Wall -g -c demo.c -o demo.o
$ gcc -g -o demo_share demo.o -L. -lhello
$ LD_LIBRARY_PATH="." ./demo_share
Hello, library world.
```

## 3.3 使用动态加载库

使用动态加载库源码
``` c
/* demo_dynamic.c -- demonstrate dynamic loading and
   use of the "hello" routine */


/* Need dlfcn.h for the routines to
   dynamically load libraries */
#include <dlfcn.h>

#include <stdlib.h>
#include <stdio.h>

/* Note that we don't have to include "libhello.h".
   However, we do need to specify something related;
   we need to specify a type that will hold the value
   we're going to get from dlsym(). */

/* The type "simple_demo_function" describes a function that
   takes no arguments, and returns no value: */

typedef void (*simple_demo_function)(void);


int main(void) {
 const char *error;
 void *module;
 simple_demo_function demo_function;

 /* Load dynamically loaded library */
 module = dlopen("libhello.so", RTLD_LAZY);
 if (!module) {
   fprintf(stderr, "Couldn't open libhello.so: %s\n",
           dlerror());
   exit(1);
 }

 /* Get symbol */
 dlerror();
 demo_function = dlsym(module, "hello");
 if ((error = dlerror())) {
   fprintf(stderr, "Couldn't find hello: %s\n", error);
   exit(1);
 }

 /* Now call the function in the DL library */
 (*demo_function)();

 /* All done, close things cleanly */
 dlclose(module);
 return 0;
}
```

编译使用动态库

```
$ gcc -Wall -g -c demo_dynamic.c
$ gcc -g -o demo_dynamic demo_dynamic.o -ldl
$ LD_LIBRARY_PATH="." ./demo_dynamic
Hello, library world.
```

## 3.4 一些对比

程序在使用静态库的时候，会将静态库编译到程序中，会导致编译后的程序比较大。

而程序在使用动态库非静态编译时，并没有将库文件加入到编译后的程序，能够相对节省空间。

下面我们通过工具来看看具体的情况。

### 3.4.1 程序使用静态库编译

通过readelf工具可以看到demo_static不依赖
```
$ readelf -d demo_static

Dynamic section at offset 0x6f8 contains 20 entries:
  Tag        Type                         Name/Value
 0x0000000000000001 (NEEDED)             Shared library: [libc.so.6]
 ...
```

### 3.4.2 程序使用动态库动态编译

通过readelf工具可以看到demo_share还依赖libhello.so
```
$ readelf -d demo_share

Dynamic section at offset 0x798 contains 21 entries:
  Tag        Type                         Name/Value
 0x0000000000000001 (NEEDED)             Shared library: [libhello.so.0]
 0x0000000000000001 (NEEDED)             Shared library: [libc.so.6]
 ...
```

### 3.4.3 程序使用动态库静态编译

使用readelf查看demo_dynamic的依赖库和上面共享库一致。

### 3.4.4 程序全静态编译

```
$ gcc -g -o demo_static_compile_static demo.o -L. -lhello-static -static
$ ./demo_static_compile_static
Hello, library world.
```

### 3.4.5 各种方式编译后程序对比

至此，我们现在编译出来的有四个可执行程序，即
静态编译：`demo_static`；
共享编译：`demo_share`；
动态加载编译：`demo_dynamic`；
全静态编译：`demo_static_compile_static`；

分析他们大小可以看到：全静态编译生成的文件最大，因为他将c的库以及libhello都编译进去了；共享库编译生成的文件最小，比静态编译生成的文件要小一些，毕竟静态编译会将libhello编译到可执行程序中。

```
$ ls -lh demo_*
-rwxrwxr-x 1 gongmh gongmh  11K Nov 17 08:12 demo_dynamic
-rwxrwxr-x 1 gongmh gongmh 7.5K Nov 17 08:07 demo_share
-rwxrwxr-x 1 gongmh gongmh 7.9K Nov 17 08:05 demo_static
-rwxrwxr-x 1 gongmh gongmh 746K Nov 17 14:16 demo_static_compile_static
```

```
$ size demo_*
   text	   data	    bss	    dec	    hex	filename
   1902	    548	     24	   2474	    9aa	demo_dynamic
   1382	    508	     16	   1906	    772	demo_share
   1205	    492	     16	   1713	    6b1	demo_static
 678611	   5792	  10464	 694867	  a9a53	demo_static_compile_static
```

# 4. 总结

实际中使用库的方法可能各有不同，不一定哪种方法就好，哪种就差。需要具体根据实际的需要选择使用。全静态编译不一定就差，毕竟相对于如今动辄几十G内存的服务器来说，库文件引起的程序大小增加，可以忽略不计了。本文只是总结梳理了一下库文件的一些基础知识和普通使用方法，具体还是需要根据实际项目需要去选择。个人水平有限，有问题欢迎交流指正。


# 5. 参考

1. https://en.wikipedia.org/wiki/Library_(computing)
2. http://tldp.org/HOWTO/Program-Library-HOWTO/index.html