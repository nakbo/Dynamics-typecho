<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->import('header.php') ?>

<div class="dynamics">
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
        <div class="permalink">
            <div class="left">
                <i class="fa fa-link" aria-hidden="true"></i>
            </div>
            <div class="line"></div>
            <div class="right">
                <div class="title">
                    <?php $this->dynamics->title() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->import('footer.php') ?>
