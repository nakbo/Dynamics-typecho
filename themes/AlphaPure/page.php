<?php
/**
 * 本页面是动态的分页面
 * 这个博主调用 Dynamics_Plugin::output() 时，会输出以下内容
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

<link rel="stylesheet" href="<?php $this->option->themeUrl("dynamic_index.css?") ?>"/>
<!--
    作者注：此处的样式不做维护。具体效果如何展示，请依据自己的主题进行相应修改。因此默认的方案，有很大可能会有可能不会融入到你主站的主题中。
    所以，私以为，【【动态】主题作者】花心思维护这里是徒劳的。
    注：调用 Dynamics_Plugin::output() 时，此页面会进行运作
-->
<div class="nabo-dynamics">
    <?php while ($this->dynamics->next()) : ?>
        <div class="dynamic-content">
            <?php echo dynamicReplacer($this->dynamics); ?>
        </div>
        <div class="dynamic-meta">
            <span class="time"><a href="<?php $this->dynamics->url() ?>" no-pjax><?php $this->dynamics->date() ?>&nbsp;&nbsp;&raquo;&nbsp;详情</a></span>
        </div>
    <?php endwhile; ?>
</div>

<?php $this->dynamics->navigator() ?>
