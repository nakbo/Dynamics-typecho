<?php
include_once 'Dynamics.php';

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 权那他
 * @version 1.2
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{

    private static $dynamicInstance;

    // 激活插件
    public static function activate()
    {
        Helper::addPanel(3, 'Dynamics/manage-dynamics.php', '我的动态', '我的动态列表', 'administrator');
        Helper::addAction('dynamics-manage', 'Dynamics_Action');

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        /** @noinspection SqlResolve */
        /** @noinspection SqlNoDataSourceInspection */
        $db->query('CREATE TABLE IF NOT EXISTS `' . $prefix . 'dynamics` (
		  `did` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `authorId` int(11) DEFAULT NULL,
		  `text` text,
		  `status` varchar(16) DEFAULT "publish",
		  `created` int(10)	 DEFAULT 0,
		  `modified` int(10)  DEFAULT 0,
		  PRIMARY KEY (`did`)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');

        return _t('插件已经激活');
    }

    // 禁用插件
    public static function deactivate()
    {
        Helper::removePanel(3, 'Dynamics/manage-dynamics.php');
        Helper::removeAction('dynamics-manage');
        return _t('插件已被禁用');
    }

    /**
     * 在主题中直接调用
     *
     * @access public
     * @param null $pattern
     * @param int $num
     * @throws Typecho_Exception
     * @throws Typecho_Widget_Exception
     */
    public static function output($pattern = NULL, $num = 5)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $cssUrl = Typecho_Common::url('/Dynamics/static/dynamic.css?version=1.1', $options->pluginUrl);
        echo '<link rel="stylesheet" href="' . $cssUrl . '" />';

        $dynamics = Dynamics_Plugin::get(array(
            "pageSize" => $num
        ));
        ?>

        <?php while ($dynamics->next()) : ?>
        <li id="<?php $dynamics->did() ?>>" class="dynamics_list">
            <div class="dynamic-author" itemprop="creator" itemscope="" itemtype="http://schema.org/Person">
                <span itemprop="image"><img class="avatar" src="<?php $dynamics->avatar() ?>"
                                            alt="<?php $dynamics->authorName() ?>" width="32" height="32"></span>
                <cite class="fn" itemprop="name"><?php $dynamics->authorName() ?></cite>
            </div>
            <div class="dynamic-meta">
                <a href="#">
                    <time itemprop="dynamicTime" datetime="{date}"><?php $dynamics->created() ?></time>
                </a>
            </div>
            <div class="dynamic-content" itemprop="commentText"><?php $dynamics->contents() ?></div>
        </li>
    <?php endwhile; ?>

        <?php $dynamics->navigator() ?>

        <?php
    }

    public static function get($params = array())
    {
        $dynamics = new Dynamics(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $dynamics->parse($params);
        return $dynamics;
    }

    public static function getInstance($params = array())
    {
        if (self::$dynamicInstance == null) {
            self::$dynamicInstance = self::get($params);
        }
        return self::$dynamicInstance;
    }


    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function form($action = NULL)
    {
    }
}
