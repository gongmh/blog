---
layout: post
title:  "Redis源码分析(001)--util.c"
date:  "2017-06-30 00:18:40 +0800"
category: Redis
tags: Redis
keywords: Redis
description: ""
---

* TOC  
{:toc}  

### 1 函数stringmatchlen

规则说明和分析

![pic](https://gongmh.github.io/source/blog/pic/redis-stringmatchlen.png)  

[xmind](https://gongmh.github.io/source/blog/file/glob-stringmatchlen.xmind)

```
/* Glob-style pattern matching. */
int stringmatchlen(const char *pattern, int patternLen,
        const char *string, int stringLen, int nocase)
{
    while(patternLen) {
    	//对于pattern第一个字符
        switch(pattern[0]) {
        //1. 如果是*，匹配任意
        case '*':
        	//1.1 下一个字符仍是*，继续查找下一个pattern字符
            while (pattern[1] == '*') {
                pattern++;
                patternLen--;
            }
            //1.2 pattern字符串长度为1，则为匹配
            if (patternLen == 1)
                return 1; /* match */
            //1.3 stringLen不为零
            while(stringLen) {
            	//1.3.1 递归匹配子pattern 和 string，如果全部匹配，则为匹配
                if (stringmatchlen(pattern+1, patternLen-1,
                            string, stringLen, nocase))
                    return 1; /* match */
                //1.3.2 去除string首字符，继续匹配子string
                string++;
                stringLen--;
            }
            //1.4 string到尾部还没匹配，则为不匹配
            return 0; /* no match */
            break;
        // 2. 如果是？，分为2中情况
        case '?':
        	//2.1 string长度为0，则为不匹配
            if (stringLen == 0)
                return 0; /* no match */
            //2.2 去除string首字符，继续匹配子string
            string++;
            stringLen--;
            break;
        //3. 如果是[，分为
        case '[':
        {
            int not, match;

            pattern++;
            patternLen--;
            //3.1 pattern的下一字符如果是^,往下取一位
            not = pattern[0] == '^';
            if (not) {
                pattern++;
                patternLen--;
            }
            match = 0;
            //3.2 循环处理
            while(1) {
            	//3.2.1 如果pattern下一字符是\，则匹配下一字符
                if (pattern[0] == '\\') {
                    pattern++;
                    patternLen--;
                    if (pattern[0] == string[0])
                        match = 1;
                //3.2.2 如果pattern下义字符是]，则跳出循环，继续匹配
                } else if (pattern[0] == ']') {
                    break;
                //3.2.3 如果pattern长度为0，pattern回退一位，跳出循环，继续匹配
                } else if (patternLen == 0) {
                    pattern--;
                    patternLen++;
                    break;
                //3.2.4 如果pattern下一位是-，并且长度大于等于3
                } else if (pattern[1] == '-' && patternLen >= 3) {
                    int start = pattern[0];
                    int end = pattern[2];
                    int c = string[0];
                    if (start > end) {
                        int t = start;
                        start = end;
                        end = t;
                    }
                    if (nocase) {
                        start = tolower(start);
                        end = tolower(end);
                        c = tolower(c);
                    }
                    pattern += 2;
                    patternLen -= 2;
                    if (c >= start && c <= end)
                        match = 1;
                //3.2.5 其他情况，直接比对pattern和string
                } else {
                    if (!nocase) {
                        if (pattern[0] == string[0])
                            match = 1;
                    } else {
                        if (tolower((int)pattern[0]) == tolower((int)string[0]))
                            match = 1;
                    }
                }
                pattern++;
                patternLen--;
            }
            if (not)
                match = !match;
            //3.3 match为0，则为不匹配
            if (!match)
                return 0; /* no match */
            string++;
            stringLen--;
            break;
        }
        //4. 如果是\，继续执行default
        case '\\':
        	//4.1 如果pattern长度大于等于2，去除这个字符继续匹配
            if (patternLen >= 2) {
                pattern++;
                patternLen--;
            }
            /* fall through */
        //5. 其他情况无正则关键字，直接比对字符，若不相等则为不匹配，否则继续
        default:
            if (!nocase) {
                if (pattern[0] != string[0])
                    return 0; /* no match */
            } else {
                if (tolower((int)pattern[0]) != tolower((int)string[0]))
                    return 0; /* no match */
            }
            string++;
            stringLen--;
            break;
        }
        pattern++;
        patternLen--;
        //6. 如果string长度为0， 循环判断pattern
        if (stringLen == 0) {
            while(*pattern == '*') {
                pattern++;
                patternLen--;
            }
            break;
        }
    }
    //7. 全部匹配完成
    if (patternLen == 0 && stringLen == 0)
        return 1;
    return 0;
}

```