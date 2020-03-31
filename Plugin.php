<?php

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 权那他
 * @version 1.1
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{
    // 激活插件
    public static function activate()
    {
        Helper::addPanel(3, 'Dynamics/manage/dynamic.php', '我的动态', '我的动态列表', 'administrator');

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
        Helper::removePanel(3, 'Dynamics/manage/dynamic.php');
        return _t('插件已被禁用');
    }

    /**
     * 在主题中直接调用
     *
     * @access public
     * @param null $pattern
     * @param int $num
     * @return string
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     * @noinspection PhpUndefinedFieldInspection
     */
    public static function output($pattern = NULL, $num = 5)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['Dynamics'])) {
            return '我的动态插件未激活';
        }

        $db = Typecho_Db::get();
        $options = Typecho_Widget::widget('Widget_Options');
        $request = Typecho_Request::getInstance();
        $page = $request->get('page', 1);
        $pageSize = intval($num);

        $select = $db->select('table.dynamics.did',
            'table.dynamics.authorId',
            'table.dynamics.text',
            'table.dynamics.status',
            'table.dynamics.created',
            'table.dynamics.modified',
            'table.users.screenName',
            'table.users.mail')->from('table.dynamics');
        $select->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);

        $select = $select->order('table.dynamics.created', Typecho_Db::SORT_DESC);
        $select = $select->page($page, $pageSize);
        $dynamics = $db->fetchAll($select);

        include_once 'Dynamics_Page.php';
        $count = $db->select('count(1) AS count')->from('table.dynamics');
        $count = $db->fetchAll($count)[0]['count'];
        $pageLayout = new Dynamics_Page($pageSize, $count, $page, 4,
            array(
                'status' => 'all'
            ), false
        );

        $str = "";
        if (empty($pattern)) {
            $cssUrl = Typecho_Common::url('/Dynamics/static/dynamic.css?version=1.1', $options->pluginUrl);
            $str .= '<link rel="stylesheet" href="' . $cssUrl . '" />';
            $pattern = "
<li id=\"dynamic-{did}\" class=\"dynamics_list\">
<div class=\"dynamic-author\" itemprop=\"creator\" itemscope=\"\" itemtype=\"http://schema.org/Person\">
	<span itemprop=\"image\"><img class=\"avatar\" src=\"{avatar}\" alt=\"{name}\" width=\"32\" height=\"32\"></span>
	<cite class=\"fn\" itemprop=\"name\">{screenName}</cite>
</div>
<div class=\"dynamic-meta\">
	<a href=\"#\"><time itemprop=\"dynamicTime\" datetime=\"{date}\">{date}</time></a>
</div>
<div class=\"dynamic-content\" itemprop=\"commentText\">{text}</div>
</li>";
        }

        foreach ($dynamics as $dynamic) {
            $avatar = "https://gravatar.loli.net/avatar/" . md5($dynamic['mail']);
            $str .= str_replace(
                array('{did}', '{avatar}', '{authorId}', '{mail}', '{screenName}', '{text}', '{status}', '{created}', '{modified}', '{date}'),
                array($dynamic['did'], $avatar, $dynamic['authorId'], $dynamic['mail'], $dynamic['screenName'], Markdown::convert(trim($dynamic['text'])), $dynamic['status'], $dynamic['created'], $dynamic['modified'], date($options->commentDateFormat, $dynamic['created'])),
                $pattern
            );
        }

        return $str . "<ol class=\"dynamics-page-navigator\">" . $pageLayout->show() . "</ol>";

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
