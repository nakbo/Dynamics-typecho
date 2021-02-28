<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * AlphaPure 动态皮肤
 *
 *
 * @package AlphaPure
 * @author ShangJixin
 * @version 1.1
 * @dependence Dynamics
 * @link https://github.com/ShangJixin
 */

//调用动态主题的文件
$this->import("header.php");
?>
<h1 class="miui-style">我的动态</h1>
<div class="nabo-dynamics">
    <?php while ($this->dynamics->next()) : ?>
        <div class="dynamic-content">
            <?php echo dynamicReplacer($this->dynamics); ?>
        </div>
        <div class="dynamic-meta">
            <span class="time"><a
                        href="<?php $this->dynamics->url() ?>"><?php $this->dynamics->date($this->option->timeFormat) ?>&nbsp;in&nbsp;<?php $this->dynamics->deviceTag() ?>&nbsp;&raquo;&nbsp;详情</a></span>
        </div>
    <?php endwhile; ?>
</div>

<?php $this->dynamics->navigator() ?>

<?php $this->import("footer.php"); ?>
