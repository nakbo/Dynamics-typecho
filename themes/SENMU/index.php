<?php
/**
 * 这是[我的动态]插件的模板
 * 本页面是动态的首页
 *
 * @package SENMU
 * @author 森木志 【基于ShangJixin二次修改】
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
<div class="nabo-dynamics" style=""margin：0 auto;>

<?php while ($this->dynamics->next()) : ?>
    <div class="dynamic-content">
<p><img src="<?php $this->dynamics->avatar(32) ?>" class="smzimg" style="margin-right:10px;"><?php $this->dynamics->authorName();?><br><?php $this->dynamics->created() ?></p>
<div style=" margin:15px 5px 10px 55px;"><?php echo dynamicReplacer($this->dynamics->content); ?></div>
    </div>

    
<?php endwhile; ?>
</div>

<?php $this->dynamics->navigator() ?>

<?php include('footer.php'); ?>
