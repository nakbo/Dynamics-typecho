<?php
/**
 * 本页面是动态的单个页面
 * 类似于博客的文章页面，这个显示单个指定的动态，根据单个动态的链接访问才显示
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

<?php $this->import("header.php") ?>

<ul>
    <li>=================================================</li>
    <li>did: <?php $this->dynamics->did() ?></li>
    <li>mail: <?php $this->dynamics->mail() ?></li>
    <li>avatar: <?php $this->dynamics->avatar() ?></li>
    <li>authorId: <?php $this->dynamics->authorId() ?></li>
    <li>authorName: <?php $this->dynamics->authorName() ?></li>
    <li>url: <?php $this->dynamics->url() ?></li>
    <li>created: <?php $this->dynamics->created('n\月j\日,Y  H:i:s') ?></li>
    <li>modified: <?php $this->dynamics->modified() ?></li>
    <li>data: <?php $this->dynamics->date('n\月j\日,Y  H:i:s') ?></li>
    <li>deviceTag: <?php $this->dynamics->deviceTag() ?></li>
    <li>deviceInfo: <?php $this->dynamics->deviceInfo() ?></li>
    <li>deviceOs: <?php $this->dynamics->deviceOs() ?></li>
    <li>content: <?php $this->dynamics->content() ?></li>
    <li>agent: <?php $this->dynamics->agent() ?></li>
    <li>status: <?php $this->dynamics->status() ?></li>
    <li>----------------------------------------------</li>
    <li>did: <?php echo $this->dynamics->did ?></li>
    <li>mail: <?php echo $this->dynamics->mail ?></li>
    <li>authorId: <?php echo $this->dynamics->authorId ?></li>
    <li>authorName: <?php echo $this->dynamics->authorName ?></li>
    <li>url: <?php echo $this->dynamics->url ?></li>
    <li>created: <?php echo $this->dynamics->created ?></li>
    <li>modified: <?php echo $this->dynamics->modified ?></li>
    <li>content: <?php echo $this->dynamics->content ?></li>
    <li>agent: <?php echo $this->dynamics->agent ?></li>
    <li>status: <?php echo $this->dynamics->status ?></li>

    <li>====================================================</li>
</ul>

<?php $this->import("footer.php") ?>
