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

// 动态首页
//$this->homeUrl();

// 注意有括号()，是echo，没有括号的调用return
//$this->dynamics->did(); //  动态的did
//$this->dynamics->did 也可以这样调用
//$this->dynamics->avatar(); // 作者的头像，支持参数 $this->dynamics->avatar(32);   $this->dynamics->avatar(32,'X','mm');
//$this->dynamics->avatar 也可以这样调用
//$this->dynamics->authorName(); //作者的用户名
//$this->dynamics->authorName
//$this->dynamics->authorId(); //作者id
//$this->dynamics->authorId
//$this->dynamics->created(); //动态创建时间 支持参数 $this->dynamics->created("n\月j\日,Y  H:i:s");
//$this->dynamics->created
//$this->dynamics->modified(); // 动态更新时间
//$this->dynamics->modified
//$this->dynamics->status(); // 动态状态，目前有publish和private
//$this->dynamics->status
//$this->dynamics->url(); // 动态的链接
//$this->dynamics->url
//$this->dynamics->content(); //动态内容，已markdown解析过后
//$this->dynamics->content
//$this->dynamics->text(); //动态内容，没有经过markdown解析
//$this->dynamics->text

// 注意上述的$this->dynamics->
// 在index.php 中是 dynamics (有s)
// 在post.php 中是 dynamic (无s，是单数)

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
            <span><?php $this->dynamics->deviceTag() ?></span>
        </div>
        <div class="dynamic-content" itemprop="commentText"><?php $this->dynamics->content() ?></div>
    </li>
<?php endwhile; ?>

<?php $this->dynamics->navigator() ?>

<?php $this->need("footer.php") ?>
