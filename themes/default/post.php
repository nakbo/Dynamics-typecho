<?php
/**
 * 本页面是动态的单个页面
 * 类似于博客的文章页面，这个显示单个指定的动态，根据单个动态的链接访问才显示
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

<?php $this->need("header.php") ?>

    <li id="<?php $this->dynamic->did() ?>>" class="dynamics_list">
        <div class="dynamic-author" itemprop="creator" itemscope="" itemtype="http://schema.org/Person">
                <span itemprop="image"><img class="avatar" src="<?php $this->dynamic->avatar() ?>"
                                            alt="<?php $this->dynamic->authorName() ?>" width="32" height="32"></span>
            <cite class="fn" itemprop="name"><?php $this->dynamic->authorName() ?></cite>
        </div>
        <div class="dynamic-meta">
            <a href="<?php $this->dynamic->url() ?>">
                <time itemprop="dynamicTime" datetime="{date}"><?php $this->dynamic->created() ?></time>
            </a>
            <span><?php $this->dynamic->deviceTag() ?></span>
        </div>
        <div class="dynamic-content" itemprop="commentText"><?php $this->dynamic->content() ?></div>
    </li>

<?php $this->need("footer.php") ?>
