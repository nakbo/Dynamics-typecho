<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->import('header.php');
?>


<h1 class="miui-style">动态内容</h1>
<div class="nabo-dynamics">
    <div class="dynamic-content">
        <?php echo dynamicReplacer($this->dynamic->content); ?>
    </div>
    <div class="dynamic-meta">
        <span class="time"><a href="<?php $this->dynamic->url() ?>"><?php $this->dynamic->created() ?></a></span>
    </div>
</div>

<?php $this->import("footer.php") ?>


