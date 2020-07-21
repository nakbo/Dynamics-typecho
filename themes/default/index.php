<?php
/**
 * 这是我的动态一套默认皮肤
 *
 * 本页面是动态的首页
 * 类似于博客的首页
 *
 * @package default
 * @author 权那他
 * @version 1.0
 * @link https://github.com/kraity/Dynamics
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// $this->need("header.php"); 是调用博客主题的文件
// $this->import("header.php"); 是调用动态主题的文件

// $this->options->siteUrl();  这些方法依然可以调用
// $this->options->themeUrl();

?>

<?php $this->need("header.php") ?>

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

<?php $this->need("footer.php") ?>
