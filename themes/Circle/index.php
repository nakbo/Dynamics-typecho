<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 动态圈子主题
 *
 *
 * @package Circle
 * @author 陆之岇
 * @version 1.0
 * @dependence Dynamics
 * @link https://github.com/krait-team/Dynamics-typecho
 */
$this->import('header.php');
?>
<div class="dynamics">
    <?php while ($this->dynamics->next()) : ?>
        <div class="dynamic">
            <div class="author">
                <div class="avatar">
                    <img src="<?php $this->dynamics->avatar() ?>" alt="avatar"/>
                </div>
                <div class="intro">
                    <div class="nickname"><?php $this->dynamics->author() ?></div>
                    <div class="metas"><?php $this->dynamics->date($this->option->timeFormat) ?>
                        &nbsp;in&nbsp;<?php $this->dynamics->deviceTag() ?></div>
                </div>
            </div>
            <div class="message">
                <?= dynamic_lazyload_filter(
                    $this->dynamics->content
                ); ?>
            </div>
            <a class="permalink" href="<?php $this->dynamics->permalink() ?>">
                <div class="left">
                    <i class="fa fa-link" aria-hidden="true"></i>
                </div>
                <div class="line"></div>
                <div class="right">
                    <div class="title">
                        <?php $this->dynamics->title() ?>
                    </div>
                </div>
            </a>
        </div>
    <?php endwhile; ?>
    <div class="navigation">
        <?php $this->dynamics->navigator() ?>
    </div>
</div>

<?php $this->import('footer.php'); ?>
