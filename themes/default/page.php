<?php
/**
 * 本页面是动态的分页面
 * 这个博主调用 Dynamics_Plugin::output() 时，会输出以下内容
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

?>

    <link rel="stylesheet" href="<?php Dynamics_Plugin::themeDirUrl("dynamic.css?version=1.3") ?>"/>

<?php while ($this->dynamics->next()) : ?>
    <li id="<?php $this->dynamics->did() ?>>" class="dynamics_list">
        <div class="dynamic-author" itemprop="creator" itemscope="" itemtype="http://schema.org/Person">
                <span itemprop="image"><img class="avatar" src="<?php $this->dynamics->avatar() ?>"
                                            alt="<?php $this->dynamics->authorName() ?>" width="32" height="32"></span>
            <cite class="fn" itemprop="name"><?php $this->dynamics->authorName() ?></cite>
        </div>
        <div class="dynamic-meta">
            <a href="<?php $this->dynamics->url() ?>">
                <time itemprop="dynamicTime"><?php $this->dynamics->created() ?></time>
            </a>
        </div>
        <div class="dynamic-content" itemprop="commentText"><?php $this->dynamics->content() ?></div>
    </li>
<?php endwhile; ?>

<?php $this->dynamics->navigator() ?>