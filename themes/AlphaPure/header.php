<?php
/**
 * 公用头部
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
include('function.php');
if ($pageType == 'index'){
    //处理标题
    if (empty($_GET['dynamicsPage'])){
        $pageTitle = '我的动态 - '.$this->options->title;
    } else {
        if (intval($_GET['dynamicsPage']) == 1) {
            $pageTitle = '我的动态 - '.$this->options->title;
        } else {
            $pageTitle = '第'.intval($_GET['dynamicsPage']).'页 - 我的动态 - '.$this->options->title;
        }
    }
    //处理描述
    $pageDescription = $this->options->description;
} elseif ($pageType == 'single') {
    //处理标题
    if (strip_tags($this->dynamic->content) == ''){
        $pageTitle = '动态内容 - '.$this->options->title;
    } else {
        $pageTitle = mb_substr(strip_tags($this->dynamic->content), 0, 10, 'utf8').'... - 动态内容 - '.$this->options->title;
    }
    //处理描述
    $pageDescription = mb_substr(strip_tags($this->dynamic->content), 0, 200, 'utf8');
} elseif ($pageType == '404'){
    $pageTitle = '页面没找到 - '.$this->options->title;
    $pageDescription = '页面没找到：错误！该路径不存在！';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1" />
<meta name="renderer" content="webkit" />
<!-- DNS预加载 -->
<meta http-equiv="x-dns-prefetch-control" content="on">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net" />
<meta content="telephone=no" name="format-detection" />
<meta content="email=no" name="format-detection" />
<title><?php echo $pageTitle; ?></title>
<meta content="<?php echo $this->options->keywords; ?>" name="keywords" />
<meta content="<?php echo $pageDescription; ?>" name="description" />
<!-- 引用CSS -->
<link rel="stylesheet" href="<?php Dynamics_Plugin::themeDirUrl("dynamic_index.css?version=1.3&time=2020.09.20.03") ?>"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
</head>
<body>
    <div id="process-container"></div>
    <div class="container" id="pjax-container">
        <div class="nav">
            <a href="<?php echo $this->options->siteUrl; ?>" target="_blank">主页</a>丨
            <a href="<?php echo $this->options->siteUrl.'dynamics/'; ?>">动态</a>丨
            <?php echo '<a href="'.$this->options->siteUrl.'dynamics/">'. $this->options->title.'&nbsp;&raquo;</a>'; ?>
        </div>
