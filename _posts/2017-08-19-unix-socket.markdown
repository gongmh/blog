---
layout: post
title:  "UNIX socket通信"
date:  "2017-08-19 00:18:40 +0800"
category: io
tags: io
keywords: io
description: ""
---

* TOC  
{:toc} 

#### 1. 引言

[套接字](https://zh.wikipedia.org/wiki/Berkeley%E5%A5%97%E6%8E%A5%E5%AD%97)（socket）主要用于实现进程间通讯，在计算机网络通讯方面被广泛使用。套接字既能满足单台计算机进程间的通信也能满足不同计算机间的通信。本文仅限于讨论TCP/IP协议栈的通信标准。

#### 2. 套接字描述符

套接字是通信端点的抽象。与应用程序要使用文件描述符访问文件一样，访问套接字也需要用套接字描述符。套接字描述符在UNIX系统是用文件描述符实现的。事实上，许多处理文件描述符的函数（如read和write）都可以处理套接字描述符。

要创建一个套接字，可以使用sockrt函数。该函数成功则返回文件（套接字）描述符，失败则返回-1.

``` c
#include <sys/socket.h>

int socket(int domain, int type, int protocol);
```

（1）参数`domain`（域）确定通信的特性，包括地址格式。

<div class="post-table">
	<table border="1" cellpadding="12" width="200" cellspacing="2">
		<tr> <th>域</th>  <th>描述</th> </tr>
    	<tr> <td>AF_INET</td>  <td>IPv4因特网域</td> </tr>
    	<tr> <td>AF_INET6</td> <td>IPv6因特网域</td> </tr>
    	<tr> <td>AF_UNIX</td> <td>UNIX域</td> </tr>
    	<tr> <td>AF_UNSPEC</td> <td>未指定</td> </tr>
	</table>
</div>

（2）参数`type`确定套接字的类型，进一步确定通信的特征。

<div class="post-table">
	<table border="1" cellpadding="12" width="500" cellspacing="2">
		<tr> <th>类型</th>  <th>描述</th> </tr>
    	<tr> <td>SOCK_DGRAM</td>  <td>长度固定，无连接的不可靠报文传递</td> </tr>
    	<tr> <td>SOCK_RAW</td> <td>IP协议的数据报接口</td> </tr>
    	<tr> <td>SOCK_SEQPACKET</td> <td>长度固定，有序、可靠的面向连接报文传递</td> </tr>
    	<tr> <td>SOCK_STREAM</td> <td>有序、可靠、双向的面向连接字节流</td> </tr>
	</table>
</div>


（3）参数`protocol`通常是0，表示按给定的域和套接字类型选择默认协议。

	当对同一域和套接字类型支持多个协议时，可以使用protocol指定一个特定的协议。在AF_INET通信域中套接字类型SOCK_STREAM的默认协议是TCP（传输控制协议）。在SOCK_DGRAM通信域中套接字类型SOCK_DGRAM的默认协议是UDP（用户数据报协议）。

	对于数据报（SOCK_DGRAM）接口，与对方通信时是不需要逻辑链接的。只需要送出一个报文，其地址是一个对方进程所使用的套接字。因此数据报提供的是无连接的服务。数据报是一种自包含报文。每个发送的报文都是独立、无序，并且可能会有丢包，每个报文可能会发送给不通的接收方。

	字节流（SOCK_STREAM）要求在交换数据之前，在本地套接字和与之通信的远程套接字之间建立一个逻辑连接。每个连接都是端到端的通信信道。会话中不包含地址信息。SOCK_STREAM提供的是字节流服务，当从套接字读取数据时，需要经过若干次函数调用才能获取发送来的所有数据。

调用socket与调用open类似，均可获得用于输入/输出的文件描述符。当不再需要该文件描述符时，调用close来关闭对文件或套接字的访问，并且释放该文件描述符以便重新使用。

虽然套接字描述符本质上是一个文件描述符，但不是所有参数为文件描述符的函数都可以接受套接字描述符。例如，由于套接字不支持文件偏移量lseek不能处理套接字描述符。

套接字通信是双向的，可以通过函数`shutdown`来禁止套接字的输入/输出。该函数成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int shutdown(int sockfd, int how);
```
如果how是SHUT_RD（关闭读端），则无法从套接字独处数据；如果how是SHUT_WR（关闭写端），则无法使用套接字发送数据；如果how是SHUT_RDWR，则同事无法读取和发送数据。既然close可以关闭套接字，为什么还要使用shutdown？首先，close只有在最后一个活动引用被关闭时才释放网络端点，而shutdown允许使用套接字处于不活动的状态；其次，关闭套接字双向传输中的一个方向会给通信带来许多便利。

#### 3. 将套接字与地址绑定

对于服务器来说，需要给一个接收客户端请求的套接字绑定一个众所周知的地址。客户端则需要在`/etc/services`或某个名字服务（name service）中注册服务器地址。

在服务端，可以用bind函数将地址绑定到一个套接字。该函数成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int bind(int sockfd, const struct sockaddr *addr, socketlen_t len);
```

对于所使用的地址有一些限制：

	在进程运行的机器上，指定的地址必须有效，不能指定一个其他机器的地址；
	地址必须和创建套接字时的地址族所支持的格式相匹配；
	端口号必须不小于1024，除非改进成具有响应的权限（超级用户）；
	一般只有套接字端点能够与地址绑定（尽管有些协议徐允许多重绑定）。

对于因特网域，如果指定IP地址为INADDR_ANY，套接字端点可以被绑定到都有的系统网络接口，即可以接收到系统所有网卡的数据包。

可以调用函数getsockname来获取绑定到一个套接字的地址。成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int getsockname(int sockfd, struct sockaddr *restrict addr, socklen_t *restrict alenp);
```

调用getsockname之前，设置alenp为一个指向整数的指针，该整数指定缓冲区sockaddr的大小。返回时，该整数会被设置成返回地址的大小。如果当前没有绑定到该套接字的地址，其结果没有定义。

如果套接字已经和对方连接，调用getpeername来获取对方地址。该函数成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int getpeername(int sockfd, struct sockaddr * restrict addr, socklen_t *erstrict alenp);
```

除了返回对方的地址之外，函数getpeername和getsockname一样。

#### 4. 建立连接

如果是面向连接的网络服务（SOCK_STREAM或SOCK_SEQPACKET），在开始交换数据之前，需要在请求服务的进程套接字（客户端）和提供服务的进程套接字（服务器）之间建立一个连接。可以使用connect建立连接。该函数成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int connect(int sockfd, const struct sockaddr *addr, socklen_t len);
```

在connect中所指定的地址是将要通信的服务器地址。如果sockfd没有绑定到一个地址，connect会给调用者绑定一个默认地址。

函数connect还可以用于无连接的网络服务（SOCK_DGRAM）。如果在SOCK_DGRAM套接字上调用connect，所有发送报文的目标地址设为connect调用中指定的地址，这样每次传送报文时就不用再提供地址。但是只能接收来自指定地址的报文。

服务器调用listen来宣告可以接受连接请求。该函数成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int listen(int sockfd, int backlog);
```

参数backlog提供了一个提示，用于表示改进成所要入队的连接请求数量。其实际值由系统决定，上限在`<sys/socket.h>`中的SOMAXCONN指定（对于TCP，其默认值为128）。

一旦队列满，系统会拒绝多余的连接请求，所以backlog的值应该基于服务器期望负载和接受连接数与启动服务的处理能力来选择。

一旦服务器调用了listen，套接字就能接收连接请求。使用accept函数来获得连接请求并建立连接。该函数成功返回文件（套接字）描述符，失败则返回-1。

``` c
#include <sys/socket.h>

int accept(int sockfd, struct sockaddr *restrict addr, socklen_t *restrict len);
```

函数accept返回的文件描述符是套接字描述符，该描述符连接到调用connect的客户端。这个新的套接字描述符和原始套接字（sockfd）具有相同的套接字类型和地址族。传给accept的原始套接字没有关联到这个连接，而是继续保持可用状态并接受其他连接请求。

如果不关心客户端标识，可以将参数addr和len设置为NULL。否则，在调用accept之前，应将参数addr设置为足够大的缓冲区来存放地址，len设置为该缓冲区大小的证书的指针。

如果没有连接请求等待处理，accept会阻塞直到一个请求到来。如果sockfd处于非阻塞模式，accept会返回-1并将errno设置为EAGAIN或EWOULDBLOCK。

#### 5. 数据传输

只要建立连接，就可以使用read和write来通过套接字通信。尽管可以使用read和write交换数据，但是socket提供了六个套接字函数来进行通信。

最简单的是send函数，可以指定标志来改变出来传输数据的方式。该函数成功返回发送的字节数，失败返回-1。

``` c
#include <sys/socket.h>

ssize_t send(int sockfd, const void *buf, size_t nbytes, int flags);
```

使用send时套接字必须已经连接。参数flags标志如下：

<div class="post-table">
	<table border="1" cellpadding="12" width="500" cellspacing="2">
		<tr> <th>标志</th>  <th>描述</th> </tr>
    	<tr> <td>MSG_DONTROUTE</td>  <td>勿将数据路由出本地网络</td> </tr>
    	<tr> <td>MSG_DONTWAIT</td> <td>允许非阻塞操作</td> </tr>
    	<tr> <td>MSG_EOR</td> <td>如果协议支持，此为记录结束</td> </tr>
    	<tr> <td>MSG_OOB</td> <td>如果协议支持，发送带外数据</td> </tr>
	</table>
</div>

如果send成功返回，斌不能表示连接另一端的进程接收到数据。只能保证send成功返回时，数据已经无错误地发送到网络上。

对于支持为报文设限的协议，如果单个报文超过协议所支持的最大尺寸，send失败并将errno设置为EMSCSIZE；对于字节流协议，send会阻塞知道整个数据被传输。

函数sendto和send很类似，区别在于sendto允许在无连接的套接字上指定一个目标地址。该函数成功返回发送的字节数，失败返回-1。

``` c
#include <sys/socket.h>

ssize_t sendto(int sockfd, const void *buf, size_t nbytes, int flags, const struct sockaddr *destaddr, socklen_t destlen);
```

面向连接的套接字，目标地址是可以忽略的，因为目标地址蕴含在连接中。对于无连接的套接字，不能使用send，除非在调用connect时预先设定了目标地址，或者采用sendto来提供。

可以调用带有msghdr结构的sendmsg来指定多重缓冲区传输数据。该函数成功返回发送的字节数，失败返回-1。

``` c
#include <sys/socket.h>

ssize_t sendmsg(int sockfd, const struct msghdr *msg, size_t nbytes, int flags);
```

接收数据可以使用recv函数。成功返回以字节计数的消息长度，若无可用消息或者对方已经按序结束则返回0，失败则返回-1。

``` c
#include <sys/socket.h>

ssize_t recv(int sockfd, void * buf, size_t nbytes, int flags);
```

调用标识参数flags标志如下：

<div class="post-table">
	<table border="1" cellpadding="12" width="500" cellspacing="2">
		<tr> <th>标志</th>  <th>描述</th> </tr>
    	<tr> <td>MSG_OOB</td>  <td>如果协议支持，发送带外数据</td> </tr>
    	<tr> <td>MSG_PEEK</td> <td>返回报文内容而不真正取走报文</td> </tr>
    	<tr> <td>MSG_TRUNC</td> <td>即使报文被截断，要求返回的是报文的实际长度</td> </tr>
    	<tr> <td>MSG_WAITALL</td> <td>等待直到所有的数据可用（仅SOCK_STREAM）</td> </tr>
	</table>
</div>

	当指定MSG_PEEK标志时，可以查看下一个要读的数据但不会真正取走。当再次调用read或者recv函数时会返回刚才查看的数据。
	对于SOCK_DGRAM和SOCK_SEQPACKET套接字，标志MSG_WAITALL无影响，因为这些基于报文的套接字类型，一次读取就返回整个报文；对于SOCK_STREAM套接字，MSG_WAITALL会等所需数据全部收到，recv函数才会返回。

如果发送者已经调用shutdown来结束传输，或者网络协议支持默认的顺序关系并且发送端已经关闭，那么当所有的数据接收完毕后，recv返回0。

接收数据，recvfrom函数可以获取数据发送者的源地址。成功返回以字节计数的消息长度，若无可用消息或者对方已经按序结束则返回0，失败则返回-1。

``` c
#include <sys/socket.h>

ssize_t recvfrom(int sockfd, void *restrict buf, size_t len, int flags, struct sockaddr *restrict addr, socklen_t * restrict addrlen);
```

如果addr非空，则其包含数据发送者的套接字端点地址。recvfrom通常用于无连接的套接字。

为了将接收到的数据送入多个缓冲区，或者希望接收辅助数据，可以使用recvmsg。

``` c
#include <sys/socket.h>

ssize_t recvmsg(int sockfd, struct msghdr *msg, int flags);
```

结构msghdr被recvmsg用于指定接收数据的输入缓冲区。

#### 6. 套接字选项

套接字机制提供两个套接字选项接口来控制套接字行为。可以获取或设置三种选项
	
	通用选项，工作在所用的套接字类型上；
	在套接字层次管理的选项，但是依赖下层协议的支持；
	特定与某协议的选项，为每个协议所独有。

setsockopt用来设置选项，成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int setsockopt(int sockfd, int level, int option, const void *val, socklen_t len);
```

getsockopt用来设置选项，成功返回0，失败则返回-1。

``` c
#include <sys/socket.h>

int getsockopt(int sockfd, int level, int option, void *restrict val, socklen_t *restirct lenp);
```

#### 7. 带外数据

带外数据（Out-of-band data）是一些通信协议所支持的可选特征，允许更高优先级的数据比普通数据有限传输。即使传输队列已经有数据，带外数据先行传输。TCP支持带外数据，但是UDP不支持。

#### 8. 非阻塞和异步I/O

通常，recv函数没有数据可用时会阻塞等待。同样，当套接字输出队列没有足够的空间来发送消息时函数send会阻塞。在套接字非阻塞模式下，行为会改变。在这些情况下，这些函数不会阻塞而是返回失败，设置errno为EWOULDBLOCK或者EAGAIN。当这些发生时，可以使用poll或select来判断何时能接收或者传输数据。

在基于套接字的异步I/O中，当能够从套接字中读取数据或者套接字写队列中的空间变得可用时，可以安排发送信号SIGIO。通过两个步骤来使用异步I/O：

	（1）建立套接字拥有者关系，信号可以被传送到合适的进程，有三种方式：
		a.在fcntl使用F_SETOWN命令；
		b.在ioctl中使用FIOSETOWN命令；
		c.在ioctl中使用SIOCSPGRP命令。
	（2）通知套接字当I/O操作不会阻塞时发信号告知，有两种方式：
		a.在fcntl中使用F_SETFL命令并启动文件标志O_ASYNC；
		b.在ioctl中使用FIOASYNC。


#### 9. 总结

如何选择合适的套接字类型？何时采用面相俩节的套接字，何时采用无连接的套接字呢？答案取决于要做的服务以及对错误的容忍程度。

包的最大尺寸是通信协议的特性。对于无连接的套接字，数据包到来可能已经没有次序，因此当所有的数据不能放在一个包里的时候，在应用程序中必须关心包的次序。对于无连接的套接字，包可能丢失。如果应用程序不能容忍这种丢失，则必须使用面向连接的套接字。

容忍丢包意味着两种选择。一种是对包进行编号，如果发现丢包则要求对方重新传输，并且识别重复包。另一种是让用户重试来处理错误。

面向连接的套接字的缺点在于需要更多的时间和工作来建立一个连接，并且每个连接需要从操作系统中消耗更多资源。


如下分别是TCP 和 UDP socket通信的流程图示。

<div>
	<div class="div-inline">
		<img src="/blog/assets/unix_socket/tcp-socket-flow.png" style="width:450px" alt="tcp" />
	</div>
	<div class="div-inline">
		<img src="/blog/assets/unix_socket/udp-socket-flow.png" style="width:450px" alt="udp" />
	</div>
</div>

<!-- <img src="/blog/assets/unix_socket/udp-socket-flow.png" style="width:600px" alt="udp" />]( /blog/assets/unix_socket/udp-socket-flow.png ) -->



