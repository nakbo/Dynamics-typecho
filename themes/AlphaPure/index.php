<?php
/**
 * 这是[我的动态]插件的一套皮肤
 *
 * 本页面是动态的首页
 * 类似于博客的首页
 *
 * @package AlphaPure
 * @author ShangJixin
 * @version 1.0
 * @link https://github.com/kraity/Dynamics
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// $this->need("header.php"); 是调用博客主题的文件
// $this->import("header.php"); 是调用动态主题的文件

// $this->options->siteUrl();  这些方法依然可以调用
// $this->options->themeUrl();

$dynamics = Dynamics_Action::getDynamics();
$pageType = 'index';
include('header.php');
?>
<h1 class="miui-style">我的动态</h1>
<div class="nabo-dynamics">
<?php while ($this->dynamics->next()) : ?>
    <div class="dynamic-content">
        <?php echo dynamicReplacer($this->dynamics->content); ?>
    </div>
    <div class="dynamic-meta">
        <span class="time"><a href="<?php $this->dynamics->url() ?>"><?php $this->dynamics->created() ?>&nbsp;&nbsp;&raquo;&nbsp;详情</a></span>
    </div>
<?php endwhile; ?>
</div>

<?php $this->dynamics->navigator() ?>

<?php include('footer.php'); ?>
