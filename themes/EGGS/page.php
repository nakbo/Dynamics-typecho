<?php
/**
 * 本页面是动态的分页面
 * 这个博主调用 Dynamics_Plugin::output() 时，会输出以下内容
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
include('function.php');
?>

    <link rel="stylesheet" href="<?php Dynamics_Plugin::themeDirUrl("dynamic.css?version=1.3") ?>"/>

<div class="nabo-dynamics">
<?php while ($this->dynamics->next()) : ?>
    <div class="dynamic-content">
        <?php echo dynamicReplacer($this->dynamics->content); ?>
    </div>
    <div class="dynamic-meta">
        <span class="time"><a href="<?php $this->dynamics->url() ?>" no-pjax><?php $this->dynamics->created() ?>&nbsp;&nbsp;&raquo;&nbsp;详情</a></span>
    </div>
<?php endwhile; ?>
</div>


<?php $this->dynamics->navigator() ?>
