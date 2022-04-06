<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php $this->titleArchive(array(
            'post' => _t('%s'),
            'page' => _t('%s'),
        ), '', ' - '); ?><?php $this->option->title(); ?></title>
    <meta content="<?php $this->keywords(); ?>" name="keywords"/>
    <meta content="<?php $this->description(); ?>" name="description"/>
    <link rel="stylesheet" href="<?php $this->option->themeUrl('style.css') ?>"/>
    <link rel="stylesheet" href="<?php $this->option->themeUrl('theme.css') ?>"/>
    <script src="<?php $this->option->themeUrl('script.js'); ?>"></script>
    <link href="https://cdnjs.loli.net/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.loli.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <?php $this->header(); ?>
</head>
<body>

<div class="header">
    <div class="nav">
        <img class="logo" src="<?php $this->option->logoUrl(); ?>" alt="logo"/>
        <label>
            <form id="search" method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
                <input type="text" id="s" name="s" class="search" placeholder="<?php _e('搜索'); ?>"/>
            </form>
        </label>
    </div>
</div>

<div class="circle">
    <div class="navigator">
        <div class="columns">
            <div class="page">
                <a class="column-link active " href="<?php $this->options->siteUrl(); ?>">
                    <i class="fa fa-fw fa-circle-o column-icon"></i>
                    <span>首页</span>
                </a>
                <a class="column-link" href="<?php $this->option->homepage(); ?>">
                    <i class="fa fa-fw fa-circle-o column-icon"></i>
                    <span>动态首页</span>
                </a>
            </div>
        </div>
    </div>