---
layout: post
title:  "github项目推荐--gitmoji-cli"
date:  "2017-09-05 10:21:40 +0800"
category: github
tags: github
keywords: gitmoji
description: ""
---

今天推荐一个有意思的git项目`gitmoji-cli`， gitmoji是一个在git commit信息中使用mojis的工具🎉。

> A [gitmoji](https://github.com/carloscuesta/gitmoji-cli) interactive client for using gitmojis on commit messages.

下面我们看看gitmoji的功能。gitmoji能够在commit信息中添加mojis图案，使得提交信息更容易辨识。

<div class="div-inline">
	<img src="/blog/assets/gitmoji/gitmoji_commit.png" style="width:450px" alt="gitmoji" />
</div>


### 安装方法：

`$ npm i -g gitmoji-cli`

```
$ gitmoji --help


  A gitmoji client for using emojis on commit messages.

  Usage
    $ gitmoji
  Options
    --init, -i  Initialize gitmoji as a commit hook
    --remove -r Remove a previously initialized commit hook
    --config, -g Setup gitmoji-cli preferences.
    --commit, -c Interactively commit using the prompts
    --list, -l  List all the available gitmojis
    --search, -s  Search gitmojis
    --version, -v Print gitmoji-cli installed version
    --update, -u  Sync emoji list with the repo
  Examples
    $ gitmoji -l
    $ gitmoji bug linter -s

```

### 使用方法：

gitmoji主要用来生成commit信息，因此需要项目中已经进行了`git add something`。gitmoji主要通过两种方式生成commit信息。

#### 方法1：直接生成

`$ gitmoji -c`

手动执行上面命令，通过交互生成commit信息。

#### 方法2：通过commit-hook

`$ gitmoji -i`

将会生成文件 .git/hooks/prepare-commit-msg

然后每次进行`git commit`的时候就会自动调用gitmoji，交互生成commit信息。


### 其他命令

`$ gitmoji -l`	//列出所有emoji

`$ gitmoji -u`	//同步repo中的emoji
