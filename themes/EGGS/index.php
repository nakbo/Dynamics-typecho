<?php
/**
 * 这是[我的动态]插件的模板
 * 本页面是动态的首页
 *
 * @package SENMU
 * @author 森木志 【基于ShangJixin二次修改】
 * @version 1.0
 * @link https://github.com/kraity/Dynamics
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// $this->need("header.php"); 是调用博客主题的文件
// $this->import("header.php"); 是调用动态主题的文件

// $this->options->siteUrl();  这些方法依然可以调用
// $this->options->themeUrl();

$dynamics = Dynamics_Action::getDynamics();
$pageType = 'index';
include('header.php');
?>
<div class="l-grid">
  <div class="l-grid__item l-grid__item--md">
    <div class="c-card">
      <div class="c-card__header">
        <div class="c-card__title" style="margin:0 auto">我的动态</div>
          <div class="nav"><?php echo '<a href="'.$this->options->siteUrl.'" target="_blank">'. $this->options->title.'&nbsp;&raquo;</a>'; ?></div>

      </div>
    <?php while ($this->dynamics->next()) : ?>
<ul class="c-contact"><div class="c-contact__left"><div class="c-contact__content">

    <div class="c-contact__name"><?php $this->dynamics->created();?></div><?php echo dynamicReplacer($this->dynamics->content); ?></div></div><div class="l-actions contact__right"><div class="c-button c-button--primary c-button--sm c-button--delete"><?php $this->dynamics->authorName();?></div></div></ul>
    <?php endwhile; ?>

    
    </div>

                 </div>  
             </div>
        </div>


        <div>
<span> <?php $this->dynamics->navigator() ?></span>
       <?php include('footer.php'); ?>
      </div>

