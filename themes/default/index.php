<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 动态插件一套开发指南皮肤
 *
 *
 * @package default
 * @author 陆之岇
 * @version 1.2
 * @dependence Dynamics
 * @link https://github.com/krait-team/Dynamics-typecho
 */

//调用动态主题的文件
$this->import("header.php");

//调用博客主题的文件
//$this->need("header.php")

// testFunction 已在文件 functions.php 里定义了, 可自行添加函数
testFunction("这是一个测试");

if ($this->is("index")) {
    echo "主页";
} else if ($this->is("post")) {
    echo "动态页面";
} else if ($this->is("404")) {
    echo "404页面";
} else {
    echo "其他页面";
}
?>

注意区别
$this->options 是博客下的
$this->option 是动态主题下的

主题配置(functions.php -> themeConfig)
头像图: <?php $this->option->logoUrl() ?>

<?php while ($this->dynamics->next()) : ?>
    <ul>
        <li>=================================================</li>
        <li>did: <?php $this->dynamics->did() ?></li>
        <li>mail: <?php $this->dynamics->mail() ?></li>
        <li>avatar: <?php $this->dynamics->avatar() ?></li>
        <li>authorId: <?php $this->dynamics->authorId() ?></li>
        <li>authorName: <?php $this->dynamics->authorName() ?></li>
        <li>url: <?php $this->dynamics->url() ?></li>
        <li>created: <?php $this->dynamics->created('n\月j\日,Y  H:i:s') ?></li>
        <li>modified: <?php $this->dynamics->modified() ?></li>
        <li>data: <?php $this->dynamics->date('n\月j\日,Y  H:i:s') ?></li>
        <li>deviceTag: <?php $this->dynamics->deviceTag() ?></li>
        <li>deviceInfo: <?php $this->dynamics->deviceInfo() ?></li>
        <li>deviceOs: <?php $this->dynamics->deviceOs() ?></li>
        <li>content: <?php $this->dynamics->content() ?></li>
        <li>agent: <?php $this->dynamics->agent() ?></li>
        <li>status: <?php $this->dynamics->status() ?></li>
        <li>----------------------------------------------</li>
        <li>did: <?php echo $this->dynamics->did ?></li>
        <li>mail: <?php echo $this->dynamics->mail ?></li>
        <li>authorId: <?php echo $this->dynamics->authorId ?></li>
        <li>authorName: <?php echo $this->dynamics->authorName ?></li>
        <li>url: <?php echo $this->dynamics->url ?></li>
        <li>created: <?php echo $this->dynamics->created ?></li>
        <li>modified: <?php echo $this->dynamics->modified ?></li>
        <li>content: <?php echo $this->dynamics->content ?></li>
        <li>agent: <?php echo $this->dynamics->agent ?></li>
        <li>status: <?php echo $this->dynamics->status ?></li>

        <li>====================================================</li>
    </ul>
<?php endwhile; ?>

<?php

// 分页
$this->dynamics->navigator() ?>

<?php $this->import("footer.php") ?>
