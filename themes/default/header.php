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

    <link rel="stylesheet" href="<?php $this->option->themeUrl("dynamic.css") ?>"/>

    <!-- 通过自有函数输出HTML头部信息 -->
    <?php $this->header(); ?>
</head>

<body>

<div class="nav">
    <a href="<?php $this->options->siteUrl(); ?>" target="_blank">主页</a>丨
    <a href="<?php $this->option->dynamicsUrl(); ?>">动态</a>
</div>

<?php echo "第" . $this->page . "页"; ?>
