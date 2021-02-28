<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * EGGS 动态皮肤
 *
 * @package EGGS
 * @author 森木志 【基于ShangJixin二次修改】
 * @version 1.1
 * @dependence Dynamics
 * @link https://github.com/SurGrafield
 */

$this->import("header.php");
?>
<div class="l-grid">
    <div class="l-grid__item l-grid__item--md">
        <div class="c-card">
            <div class="c-card__header">
                <div class="c-card__title" style="margin:0 auto">我的动态</div>
                <div class="nav"><?php echo '<a href="' . $this->options->siteUrl . '" target="_blank">' . $this->options->title . '&nbsp;&raquo;</a>'; ?></div>

            </div>
            <?php while ($this->dynamics->next()) : ?>
                <ul class="c-contact">
                    <div class="c-contact__left">
                        <div class="c-contact__content">

                            <div class="c-contact__name"><?php $this->dynamics->created(); ?></div><?php echo dynamicReplacer($this->dynamics->content); ?>
                        </div>
                    </div>
                    <div class="l-actions contact__right">
                        <div class="c-button c-button--primary c-button--sm c-button--delete"><?php $this->dynamics->authorName(); ?></div>
                    </div>
                </ul>
            <?php endwhile; ?>


        </div>

    </div>
</div>
</div>


<div>
    <span> <?php $this->dynamics->navigator() ?></span>
</div>

<?php $this->import("footer.php"); ?>

