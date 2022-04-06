<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<link rel="stylesheet" href="<?php $this->option->themeUrl('theme.css') ?>"/>
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
                <?php $this->dynamics->content() ?>
            </div>
            <a class="permalink" href="<?php $this->dynamics->permalink() ?>" target="_blank">
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
        </article>
    <?php endwhile; ?>
    <div class="navigation">
        <?php $this->dynamics->navigator() ?>
    </div>
</div>
