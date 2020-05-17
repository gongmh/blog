---
layout: post
title:  "golang基础-03-http-client"
date:   2018-12-22 15:40:18 +0800
categories: go
tags: go
author: gongmh
---

* TOC
{:toc}

# 1. 引言

针对http客户端，本文提供两个基础方法`HttpGet`和`HttpPost`。其中日志模块可以根据自己需要做更改。

client的返回值，是response的byte数组，根据实际情况做解析处理即可。

# 2. httpGet

HttpGet运行实例，**[运行一下](http://www.gongmh.com/tools/sharecode?id=30G__xgGR)**。

``` golang
func main() {
    data := map[string]string {
        "key": "value",
    }
    resp, err := HttpGet("http://httpbin.org/get", data)
    if err != nil {
        return
    }

    type RespStruct struct {
        Args map[string]string    `json:"args"`
        Url string    `json:"url"`
    }

    var rs RespStruct
    err = json.Unmarshal(resp, &rs)
    if err != nil {
        return
    }

    fmt.Println("===rs.url==: ", rs.Url)
}
```

输出结果

```
===rs.url==:  http://httpbin.org/get?key=value
```

# 3. httpPost

HttpPost运行实例，**[运行一下](http://www.gongmh.com/tools/sharecode?id=k21XlxgGg)**。

``` golang
func main() {
    data := map[string]string {
        "key": "value",
    }
    resp, err := HttpPost("http://httpbin.org/post", data)
    if err != nil {
        return
    }

    type RespStruct struct {
        Form map[string]string    `json:"form"`
        Url string    `json:"url"`
    }

    var rs RespStruct
    err = json.Unmarshal(resp, &rs)
    if err != nil {
        return
    }

    fmt.Println("===rs.form==: ", rs.Form)
}
```

输出结果

```
===rs.form==:  map[key:value]
```

# 附：http客户端lib

``` golang
package helper

import (
    "errors"
    "fmt"
    "io/ioutil"
    "log"
    "net/http"
    "net/url"
    "strings"
    "time"
)

//change your log
var logInfo = log.Println
var logFatal = log.Fatal

func HttpGet(requestUrl string, data map[string]string) (response []byte, err error) {
    client := &http.Client{
        Timeout: time.Duration(3 * time.Second),
    }
    req, err := request("GET", requestUrl, data)
    if err != nil {
        logFatal(fmt.Sprintf("http req err, err: %s", err.Error()))
        return response, err
    }
    resp, err := client.Do(req)

    if err != nil {
        logFatal(fmt.Sprintf("http request do err, err: %s", err.Error()))
        return response, err
    }
    defer resp.Body.Close()

    body, err := ioutil.ReadAll(resp.Body)
    if err != nil {
        logFatal(fmt.Sprintf("http request read io err, err: %s", err.Error()))
        return response, err
    }

    logInfo(requestUrl, data, string(body))

    return body, err
}

func HttpPost(requestUrl string, data map[string]string) (response []byte, err error) {
    client := &http.Client{
        Timeout: time.Duration(3 * time.Second),
    }
    req, err := request("POST", requestUrl, data)
    if err != nil {
        logFatal(fmt.Sprintf("http req err, err: %s", err.Error()))
        return response, err
    }
    resp, err := client.Do(req)
    if err != nil {
        logFatal(fmt.Sprintf("http request do err, err: %s", err.Error()))
        return response, err
    }
    defer resp.Body.Close()

    body, err := ioutil.ReadAll(resp.Body)
    if err != nil {
        logFatal(fmt.Sprintf("http request read io err, err: %s", err.Error()))
        return response, err
    }

    logInfo(requestUrl, data, string(body))

    return body, err
}


func request(method string, requestUrl string, data map[string]string) (req *http.Request, err error) {
    v := url.Values{}
    if len(data) > 0 {
        for key, value := range data {
            v.Add(key, value)
        }
    }

    if method == "POST" {
        req, err = http.NewRequest(method, requestUrl, strings.NewReader(v.Encode()))
    } else {
        req, err = http.NewRequest(method, requestUrl+"?"+v.Encode(), nil)
    }

    if err != nil {
        logFatal(fmt.Sprintf("new request err, err: %s", err.Error()))
        return nil, err
    }

    if method == "POST" {
        req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
    }
    return req, nil
}
```