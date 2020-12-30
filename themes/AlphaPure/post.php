<?php
/**
 * 本页面是动态的单个页面
 * 类似于博客的文章页面，这个显示单个指定的动态，根据单个动态的链接访问才显示
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$dynamic = Dynamics_Action::getDynamic();
$pageType = 'single';
include('header.php');
?>

<h1 class="miui-style">动态内容</h1>
<div class="nabo-dynamics">
    <div class="dynamic-content">
        <?php echo dynamicReplacer($this->dynamic); ?>
    </div>
    <div class="dynamic-meta">
        <span class="time"><a href="<?php $this->dynamic->url() ?>"><?php $this->dynamic->created(); ?></a>&nbsp;in&nbsp;<?php $this->dynamic->deviceTag() ?></span>
    </div>
</div>

<?php include('footer.php'); ?>
