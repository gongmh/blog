---
layout: post
title:  "socket编程中服务端重启时bind失败"
date:   2018-11-14 21:40:18 +0800
categories: linux
tags: linux socket
author: gongmh
---

* TOC
{:toc}

在做socket通信时，可能会遇到程序关掉后，重新启动的时候报bind失败的错误，然后等一段时间后启动就正常了。

bind失败的原因很简单，就是关闭的进程未将端口资源完全释放，新进程bind的时候自然就失败了。

通过netstat看到，关闭程序后，程序使用的端口还处于TIME_WAIT状态，我们通过实际的例子来看看。顺着这个例子，也学习一下tcp的TIME_WAIT。

# 1. 示例准备

启动一个简单的ECHO server进行测试，ECHO只简单对client的消息进行回显。

``` c
/*******************************************************************************
 * File Name         : echo.c
 * Desc              : echo client msg
 * Author            : gongmh
 * Create Time       : 2018年11月14日22:45:34
*******************************************************************************/
#include <sys/socket.h>
#include <netinet/in.h>
#include <stdio.h>
#include <string.h>

#define SERVER_PORT 8080

int main(int argc, char **argv) {

    // 1. create
    int listenFd = socket(AF_INET, SOCK_STREAM, 0);
    if (listenFd < 0) {
        fprintf(stderr, "Failed to create listen socket");
        return 1;
    }

    // //reuse socket
    // int reuse = 1;
    // setsockopt(listenFd, SOL_SOCKET, SO_REUSEADDR, &reuse, sizeof(reuse));

    struct sockaddr_in listenAddr;
    memset(&listenAddr, 0, sizeof(listenAddr));
    listenAddr.sin_family = AF_INET;
    listenAddr.sin_addr.s_addr = INADDR_ANY;
    listenAddr.sin_port = htons(SERVER_PORT);

    // 2. bind
    if (bind(listenFd, (struct sockaddr*)&listenAddr, sizeof(listenAddr)) < 0) {
        fprintf(stderr, "Bind failed.");
        return 1;
    }

    // 3. listen
    if (listen(listenFd, 5) < 0) {
        fprintf(stderr, "Listen socket failed.");
        return 1;
    }

    struct sockaddr_in client;
    socklen_t len = sizeof(client);

    while(1) {
        // 4. accept
        int acceptFd = accept(listenFd, (struct sockaddr*)&client, &len);
        if(acceptFd < 0) {
            fprintf(stderr, "Accept socket failed.");
            continue;
        }

        char recvBuf[1024];
        char sendBuf[1050];

        // 5. read & write
        while(1) {
            ssize_t readSize = read(acceptFd, recvBuf, sizeof(recvBuf)-1);
            if(readSize <= 0) {
                fprintf(stderr, "Read socket failed.");
                break;
            }

            recvBuf[readSize] = 0;
            sprintf(sendBuf, "Server: %s", recvBuf);
            write(acceptFd, sendBuf, sizeof(sendBuf));
        }
    }

    return 0;
}
```

编译启动ECHO服务，

```
[gongmh@f92432f51628 ~]$  gcc -o echo echo.c & ./echo
```

在另一终端通过telnet连接ECHO服务，

```
[gongmh@f92432f51628 ~]$ telnet 127.0.0.1 8080
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
hello server
client: hello server
```

然后关闭服务ECHO程序，再次启动ECHO，程序报bind错误，不能正常启动。

```
[gongmh@f92432f51628 ~]$ ./echo
Bind failed.
```

# 2. 现象分析

通过netstat来分析一下为什么会报错，首先ECHO程序启动后，还未有telnet client连入前，8080端口处于LISTEN状态。

```
[gongmh@f92432f51628 ~]$ netstat -anlp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address               Foreign Address             State       PID/Program name
tcp        0      0 0.0.0.0:8080                0.0.0.0:*                   LISTEN      21083/./echo
```

客服端通过telnet连接到服务端后，会增加一条链接已建立ESTABLISHED的记录。

```
[gongmh@f92432f51628 ~]$ netstat -anlp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address               Foreign Address             State       PID/Program name
tcp        0      0 0.0.0.0:8080                0.0.0.0:*                   LISTEN      21083/./echo
tcp        0      0 127.0.0.1:8080              127.0.0.1:46954             ESTABLISHED 21083/./echo
tcp        0      0 127.0.0.1:46954             127.0.0.1:8080              ESTABLISHED 21405/telnet
```

当服务端关闭后，LISTEN状态的记录已经不存在了，但是ESTABLISHED的记录状态变为TIME_WAIT。

这条TIME_WAIT记录会持续一段时间才会删除，可以看到，正是在这段时间内重新启动服务端，才会导致bind失败。

```
netstat -anlp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address               Foreign Address             State       PID/Program name
tcp        0      0 127.0.0.1:8080              127.0.0.1:46954             TIME_WAIT   -
```

# 3. 抓包分析

通过tcpdump抓包，分析一下整个通信流程.

```
[root@f92432f51628 /]# tcpdump -i any port 8080 -w /tmp/bind_error.pcap -vvv
```

通过包分析软件，例如wireshark，来分析一下抓取的包。

<img src="{{site.baseurl}}/assets/unix_socket/bind_error.png"  alt="bind_error" />

图中1即为tcp建立链接的3次握手过程，2是服务端和客户端通信的过程，最后3是断开链接的过程。

<img src="{{site.baseurl}}/assets/unix_socket/TCP_CLOSE.png" width="375px" alt="TCP_CLOSE" />

从图中3可以看到是8080端口主动发起的FIN动作，根据TCP的规定，主动断开链接的一端，会进入到TIME_WAIT状态。

最终导致服务端8080端口未释放，服务不能正常重启。

# 4. TIME_WAIT分析

为什么要有TIME_WAIT状态？[TCP](https://en.wikipedia.org/wiki/Transmission_Control_Protocol#Protocol_operation)中说明了TIME_WAIT。

```
 TIME-WAIT S/C
  主动关闭端接收到FIN后，就发送ACK包，等待足够时间以确保被动关闭端收到了终止请求的确认包。【按照RFC793，一个连接可以在TIME-WAIT保证最大四分钟，即最大分段寿命（maximum segment lifetime）的2倍】

```

下面我们看一下为什么要引入TIME_WAIT状态。（by [stackoverflow](https://stackoverflow.com/questions/14388706/socket-options-so-reuseaddr-and-so-reuseport-how-do-they-differ-do-they-mean-t))


>  A socket has a send buffer and if a call to the send() function succeeds, it does not mean that the requested data has actually really been sent out, it only means the data has been added to the send buffer. For UDP sockets, the data is usually sent pretty soon, if not immediately, but for TCP sockets, there can be a relatively long delay between adding data to the send buffer and having the TCP implementation really send that data. As a result, when you close a TCP socket, there may still be pending data in the send buffer, which has not been sent yet but your code considers it as sent, since the send() call succeeded. If the TCP implementation was closing the socket immediately on your request, all of this data would be lost and your code wouldn't even know about that. TCP is said to be a reliable protocol and losing data just like that is not very reliable. That's why a socket that still has data to send will go into a state called TIME_WAIT when you close it. In that state it will wait until all pending data has been successfully sent or until a timeout is hit, in which case the socket is closed forcefully.

上面主要是说，TCP通信中，应用程序调用send()后，并没有真正的发送出去，而是放在send buffer中，添加到buffer中的时间和真正发送出去可能会一个相对长的延时。TCP作为一个可靠协议，因此当关闭一个TCP socket时，TCP增加一个TIME_WAIT状态，等待对端buffer中的数据成功发送。

# 5. 解决方案

通过设置SO_REUSEADDR来设置socket忽略处于TIME_WAIT状态的链接，下面我们实际看下。

> The question is, how does the system treat a socket in state TIME_WAIT? If SO_REUSEADDR is not set, a socket in state TIME_WAIT is considered to still be bound to the source address and port and any attempt to bind a new socket to the same address and port will fail until the socket has really been closed, which may take as long as the configured Linger Time. So don't expect that you can rebind the source address of a socket immediately after closing it. In most cases this will fail. However, if SO_REUSEADDR is set for the socket you are trying to bind, another socket bound to the same address and port in state TIME_WAIT is simply ignored, after all its already "half dead", and your socket can bind to exactly the same address without any problem. In that case it plays no role that the other socket may have exactly the same address and port. Note that binding a socket to exactly the same address and port as a dying socket in TIME_WAIT state can have unexpected, and usually undesired, side effects in case the other socket is still "at work", but that is beyond the scope of this answer and fortunately those side effects are rather rare in practice.

也就是说，如果没有设置SO_REUSEADDR，处于TIME_WAIT的socket仍然认为绑定了地址和端口，其他socket尝试绑定这个地址和端口时都会失败。如果设置了SO_REUSEADDR，socket绑定地址端口时，会自动忽略处于TIME_WAIT状态的地址和端口。这可能会带来一些副作用，但是在实际中很少出现问题。

在程序层面，通过设置socket为，即打开上面源码中的注释。

``` c
    //reuse socket
    int reuse = 1;
    setsockopt(listenFd, SOL_SOCKET, SO_REUSEADDR, &reuse, sizeof(reuse));
```

再次编译执行，通过netstat可以看到，能够正常重启服务端。

```
[gongmh@f92432f51628 ~]$ netstat -anlp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address               Foreign Address             State       PID/Program name
tcp        0      0 0.0.0.0:8080                0.0.0.0:*                   LISTEN      21490/./echo
tcp        0      0 127.0.0.1:8080              127.0.0.1:47024             TIME_WAIT   -
tcp        0      0 127.0.0.1:8080              127.0.0.1:47026             ESTABLISHED 21490/./echo
tcp        0      0 127.0.0.1:47026             127.0.0.1:8080              ESTABLISHED 21491/telnet
```

另外在linux系统层面，可以修改linux内核参数，缩短TIME_WAIT的时间等，但这不是本文要讨论的内容了。到此，就了解了程序重启bind失败的原因及解决方案。