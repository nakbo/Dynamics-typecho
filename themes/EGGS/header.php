<?php
/**
 * 公用头部
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
include('function.php');
if ($pageType == 'index'){
    if (empty($_GET['dynamicsPage'])){
        $pageTitle = '我的动态 - '.$this->options->title;
    } else {
        if (intval($_GET['dynamicsPage']) == 1) {
            $pageTitle = '我的动态 - '.$this->options->title;
        } else {
            $pageTitle = '第'.intval($_GET['dynamicsPage']).'页 - 我的动态 - '.$this->options->title;
        }
    }
} elseif ($pageType == 'single') {
    if (strip_tags($this->dynamic->content) == ''){
        $pageTitle = '动态内容 - '.$this->options->title;
    } else {
        $pageTitle = mb_substr(strip_tags($this->dynamic->content), 0, 10, 'utf8').'... - 动态内容 - '.$this->options->title;
    }
} elseif ($pageType == '404'){
    $pageTitle = '页面没找到 - '.$this->options->title;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />
<meta name="renderer" content="webkit" />
<title><?php echo $pageTitle; ?></title>
<link rel="stylesheet" href="<?php Dynamics_Plugin::themeDirUrl("dynamic_index.css?version=1.3&time=2020.07.21.18") ?>"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
</head>
<body>
    <div id="process-container"></div>
    <div class="container" id="pjax-container">
      