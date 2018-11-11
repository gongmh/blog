---
layout: post
title:  "Redis源码分析(003)--事件"
date:  "2017-08-23 17:18:40 +0800"
category: redis
tags: redis
keywords: redis
description: ""
---



Redis的事件包括文件事件和时间事件，在事件处理循环中不断处理文件事件和时间事件。redis将事件做了统一封装在`ae.h`中，底层通过select、kqueue、epoll等实现，对外提供统一的api。而RPC请求的socket相关函数则封装在`networking.h`中来提供服务。整个事件过程就是针对`event loop`做添加、删除以及阻塞等待事件，在相应的事件上调用处理函数，从而完成redis的响应客户端请求、后台程序等过程。

在学习redis时间之前我们先简单回顾一下相关基础知识。我们先简单介绍一下I/O多路复用。

#### 1. I/O多路复用技术

I/O多路复用主要是为了提高io的效率，通过单线程/单进程对fdsets的读写等事件做监控，当事件到来时通过fd的回调函数做相应处理。主要实现有select、poll、kqueue和epoll等，具体[参考博文]()。

#### 2. reactor模式

wikipedia上对[Reactor design pattern](https://en.wikipedia.org/wiki/Reactor_pattern)的定义如下：

> The reactor design pattern is an event handling pattern for handling service requests delivered concurrently to a service handler by one or more inputs. The service handler then demultiplexes the incoming requests and dispatches them synchronously to the associated request handlers.

这句话是说Reactor是处理1个或多个输入同时发送服务请求的事件处理模式。Reactor通过将到来的请求多路复用，并且同步关联到相应的处理程序上。

Redis就是reactor模式的一种简单实现。通过此模式，redis提供高性能的服务响应。

#### 3. redis事件流程

我们有了上述基本概念之后再去理解redis的事件就比较简单了。Redis的核心就是`Event Loop`，同步不断的从`Event Loop`中获取已触发的事件，调用相应的回调函数进行处理的过程。

下面通过伪代码将redis的流程做一下梳理，首先我们简单介绍一下几个函数的作用：

* `main`函数是redis server的启动入口，包括创建event loop，添加event等；
* `acceptTcpHandler`、`sendReplyToClient`和`readQueryFromClient`是对应事件的回调函数；
* `beforeSleep`是每次循环进入阻塞前的处理函数；
* `aeProcessEvents`是时间阻塞函数，获取阻塞fd，调用相应的回调函数。

具体伪代码如下：

``` c
//入口
int main(){
	
	//1. 初始化server
	initServer();

	//2. 创建event loop
	aeCreateEventLoop(fdNum);

	//3. 监听端口设置非阻塞
	listenToPort(server.port,server.ipfd,&server.ipfd_count);

	//4. 添加时间事件，主要是后台操作
	aeCreateTimeEvent(server.el, 1, serverCron, NULL, NULL);

	//5. 添加文件事件，监听的fd是listen fd可读事件
	//并设置触发事件的处理函数acceptTcpHandler
	aeCreateFileEvent(server.el, server.ipfd, AE_READABLE, acceptTcpHandler,NULL);

	//6. 阻塞前的一些配置，设置sleep前的处理函数beforeSleep
	aeSetBeforeSleepProc(server.el,beforeSleep);

	//7. 处理事件
	while (!eventLoop->stop) {
		//执行事件前的工作
		eventLoop->beforesleep(eventLoop);
		//处理事件
		aeProcessEvents(eventLoop, AE_ALL_EVENTS);
	}

	//8. 删除event loop
	aeDeleteEventLoop(server.el);
}

//阻塞前的处理函数
void beforeSleep(struct aeEventLoop *eventLoop) {

	//1. 其他处理
	...

	//2. 添加文件事件，监听accept的fd的可写事件
	//并设置触发事件的处理函数sendReplyToClient
	aeCreateFileEvent(server.el, c->fd, AE_WRITABLE, sendReplyToClient, c);
}

//accept_fd可写的处理函数
void sendReplyToClient(aeEventLoop *el, int fd, void *privdata, int mask) {
	//1. 向客服端send信息
	write(fd, ((char*)o->ptr)+c->sentlen,objlen-c->sentlen);

	//2. 删除文件事件，删除accept的fd 读&写事件
	aeDeleteFileEvent(server.el,c->fd,AE_READABLE);
	aeDeleteFileEvent(server.el,c->fd,AE_WRITABLE);
}

//listen_fd可读的处理函数
void acceptTcpHandler(aeEventLoop *el, int fd, void *privdata, int mask) {
	//1. accept connect 请求
	cfd = anetTcpAccept(server.neterr, fd, cip, sizeof(cip), &cport);

	//2. 添加文件事件，监听已经accept的fd的可读事件
	//并设置触发事件的处理函数readQueryFromClient
	aeCreateFileEvent(server.el,fd,AE_READABLE,readQueryFromClient, c);
}

//accept_fd可读的处理函数
void readQueryFromClient(aeEventLoop *el, int fd, void *privdata, int mask) {
	//1. 从fd中读信息
	nread = read(fd, c->querybuf+qblen, readlen);

	//2. 如果读出错，删除fd的事件
	if(error(nread)){
		aeDeleteFileEvent(server.el,c->fd,AE_READABLE);
		aeDeleteFileEvent(server.el,c->fd,AE_WRITABLE);
	}
	
}

//事件阻塞处理函数
int aeProcessEvents(aeEventLoop *eventLoop, int flags){
	//找到第一个要触发的时间事件
	shortest = aeSearchNearestTimer(eventLoop);

	//获取事件，tvp与shortest相关，调用epoll_wait阻塞等待事件的到来
	numevents = aeApiPoll(eventLoop, tvp);

	for (j = 0; j < numevents; j++) {
		aeFileEvent *fe = &eventLoop->events[eventLoop->fired[j].fd];
        
		if (fe->mask & mask & AE_READABLE) {
			fe->rfileProc(eventLoop,fd,fe->clientData,mask);
		}
		if (fe->mask & mask & AE_WRITABLE) {
			fe->wfileProc(eventLoop,fd,fe->clientData,mask);
		}
	}

	//处理时间事件
	processTimeEvents(eventLoop);
}

```


<!-- 
#### 4. redis事件数据结构及api -->

<!-- ``` c
//event loop
aeEventLoop *aeCreateEventLoop(int setsize);
void aeDeleteEventLoop(aeEventLoop *eventLoop);
void aeStop(aeEventLoop *eventLoop);

//file event
int aeCreateFileEvent(aeEventLoop *eventLoop, int fd, int mask,
        aeFileProc *proc, void *clientData);
void aeDeleteFileEvent(aeEventLoop *eventLoop, int fd, int mask);
int aeGetFileEvents(aeEventLoop *eventLoop, int fd);

//time event
long long aeCreateTimeEvent(aeEventLoop *eventLoop, long long milliseconds,
        aeTimeProc *proc, void *clientData,
        aeEventFinalizerProc *finalizerProc);
int aeDeleteTimeEvent(aeEventLoop *eventLoop, long long id);


//处理事件
int aeProcessEvents(aeEventLoop *eventLoop, int flags);


int aeWait(int fd, int mask, long long milliseconds);

//high level event api
void aeMain(aeEventLoop *eventLoop);
char *aeGetApiName(void);
void aeSetBeforeSleepProc(aeEventLoop *eventLoop, aeBeforeSleepProc *beforesleep);
int aeGetSetSize(aeEventLoop *eventLoop);
int aeResizeSetSize(aeEventLoop *eventLoop, int setsize);
``` -->

#### 4. 总结

对于redis的事件流程，简化为流程图更加直观明了，如图所示：

[<img src="{{site.baseurl}}/assets/redis_event_loop/redis-event-loop.png" style="width:1000px" alt="sds" />]( /blog/assets/redis_event_loop/redis-event-loop.png )

<!-- 1.socket
2.IO多路复用程序
	封装epoll等
3.文件事件分派器
4.事件处理器


1.Reactor模式详解
http://www.blogjava.net/DLevin/archive/2015/09/02/427045.html
2.io多路复用对比
http://blog.csdn.net/breaksoftware/article/list/1 -->

