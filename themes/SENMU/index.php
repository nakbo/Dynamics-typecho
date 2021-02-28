<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * SENMU 动态皮肤
 *
 * @package SENMU
 * @author 森木志 【基于ShangJixin二次修改】
 * @version 1.1
 * @dependence Dynamics
 * @link https://github.com/kraity/Dynamics
 */

$this->import("header.php");
?>
<h1 class="miui-style">我的动态</h1>
<div class="nabo-dynamics" style=" margin：0 auto;">
    <?php while ($this->dynamics->next()) : ?>
        <div class="dynamic-content">
            <p><img src="<?php $this->dynamics->avatar(32) ?>" class="smzimg"
                    style="margin-right:10px;"><?php $this->dynamics->authorName(); ?>
                <br><?php $this->dynamics->created() ?></p>
            <div style=" margin:15px 5px 10px 55px;"><?php echo dynamicReplacer($this->dynamics->content); ?></div>
        </div>
    <?php endwhile; ?>
</div>

<?php $this->dynamics->navigator() ?>

<?php $this->import("footer.php"); ?>
