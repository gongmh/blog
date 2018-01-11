---
layout: post
title:  "stress介绍"
date:  "2018-01-11 11:52:17 +800"
category: linux
tags: linux
keywords: linux
description: ""
---

stress介绍

### 1. stress简介

[stress](http://people.seas.harvard.edu/~apw/stress/)是一个构造系统负载的工具，能够产生对cpu、内存、I/O、磁盘等产生负载。并且stress是用c语言开发的，非常精简。stress并不是系统压测工具[[1]](http://people.seas.harvard.edu/~apw/stress/FAQ#1-1.3 Is stress a benchmark)。

> At present stress has worker types for processor, memory, I/O, and disk.

### 2. 安装

安装方式很常规，如下所示。需要注意的是要提前安装dev tools。

```
$ wget http://people.seas.harvard.edu/~apw/stress/stress-1.0.4.tar.gz

$ tar -zxvf stress-1.0.4.tar.gz

//请提前安装dev tools
//yum groupinstall "Development tools"
$ cd stress-1.0.4 && ./configure && make && make install 
```

### 3. 使用

安装完成后，我们可以看到

```
$ stress
`stress' imposes certain types of compute stress on your system

Usage: stress [OPTION [ARG]] ...
 -?, --help         show this help statement
     --version      show version statement
 -v, --verbose      be verbose
 -q, --quiet        be quiet
 -n, --dry-run      show what would have been done
 -t, --timeout N    timeout after N seconds
     --backoff N    wait factor of N microseconds before work starts
 -c, --cpu N        spawn N workers spinning on sqrt()
 -i, --io N         spawn N workers spinning on sync()
 -m, --vm N         spawn N workers spinning on malloc()/free()
     --vm-bytes B   malloc B bytes per vm worker (default is 256MB)
     --vm-stride B  touch a byte every B bytes (default is 4096)
     --vm-hang N    sleep N secs before free (default none, 0 is inf)
     --vm-keep      redirty memory instead of freeing and reallocating
 -d, --hdd N        spawn N workers spinning on write()/unlink()
     --hdd-bytes B  write B bytes per hdd worker (default is 1GB)

Example: stress --cpu 8 --io 4 --vm 2 --vm-bytes 128M --timeout 10s

Note: Numbers may be suffixed with s,m,h,d,y (time) or B,K,M,G (size).
```

参数简单说明：

    -c // 增加process负载, 主要调用sqrt()
    -i // 增加I/O负载, 主要调用sync()
    -m // 增加memory负载, malloc()/free()
    -d // 增加disk负载, write()/unlink()

    -t // 执行命令的超时时间
    -v // 显示debug信息
    -q // 只显示错误信息


### 4. 实例
针对1核的cpu，我们做如下测试：

(1) cpu负载

```
$ stress -c 1
```

可以看到cpu的stress进程已经占满了cpu

<img src="/blog/assets/linux_stress/stress_cpu.png" style="width:700px" alt="stress_cpu" />

(2) mem负载

```
$ stress --vm 1 --vm-bytes 800M --vm-hang 0 --vm-keep
```
<img src="/blog/assets/linux_stress/stress_vm.png" style="width:700px" alt="stress_vm" />

(3) io负载

```
$ stress -i 1 
```

(4) 磁盘负载

```
$ stress -d 1 
```

<img src="/blog/assets/linux_stress/stress_hdd.png" style="width:700px" alt="stress_hdd" />