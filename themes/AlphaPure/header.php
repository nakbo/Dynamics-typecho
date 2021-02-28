<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <!-- DNS预加载 -->
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net"/>
    <meta content="telephone=no" name="format-detection"/>
    <meta content="email=no" name="format-detection"/>
    <title><?php $this->titleArchive(array(
            'post' => _t('%s'),
            'page' => _t('%s'),
        ), '', ' - '); ?><?php $this->option->title(); ?></title>
    <meta content="<?php $this->keywords(); ?>" name="keywords"/>
    <meta content="<?php $this->description(); ?>" name="description"/>
    <!-- 引用CSS -->
    <link rel="stylesheet" href="<?php $this->option->themeUrl("dynamic_index.css?version=1.3&time=2020.09.20.03") ?>"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css"/>
    <?php $this->header(); ?>
</head>
<body>
<div id="process-container"></div>
<div class="container" id="pjax-container">
    <div class="nav">
        <a href="<?php $this->options->siteUrl(); ?>" target="_blank">主页</a>丨
        <a href="<?php $this->option->dynamicsUrl(); ?>">动态</a>丨
        <a href="<?php $this->option->dynamicsUrl(); ?>"><?php $this->options->title() ?> &nbsp;&raquo;</a>
    </div>
