<?php

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 权那他
 * @version 1.0
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{
    // 激活插件
    public static function activate()
    {
//        Helper::addPanel(3, 'Dynamics/manage/dynamics.php', '我的动态', '我的动态列表', 'administrator');

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
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
//        Helper::removePanel(3, 'Dynamics/manage/dynamics.php');
        return _t('插件已被禁用');
    }

    /**
     * 在主题中直接调用
     *
     * @access public
     * @param null $pattern
     * @param int $num
     * @param int $page
     * @return string
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function output($pattern = NULL, $num = 10, $page = 1)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['Dynamics'])) {
            return '我的动态插件未激活';
        }

        $db = Typecho_Db::get();
        $options = Typecho_Widget::widget('Widget_Options');
        
        $sql = $db->select('table.dynamics.did',
            'table.dynamics.authorId',
            'table.dynamics.text',
            'table.dynamics.status',
            'table.dynamics.created',
            'table.dynamics.modified',
            'table.users.screenName',
            'table.users.mail')->from('table.dynamics');
        $sql->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);

        $sql = $sql->order('table.dynamics.created', Typecho_Db::SORT_DESC);
        $num = intval($num);
        $page = intval($page);
        /*if ($num > 0) {
            $sql = $sql->limit($num);
        }*/
        if (empty($page)){
        	$page = 1;
        }
        if ($num > 0 && $page >=1){
        	$sql = $sql->page($page,$num);
        }
        $dynamics = $db->fetchAll($sql);

        $str = "";
        if (empty($pattern)) {
	    $cssUrl = Typecho_Common::url('/Dynamics/static/dynamic.css', $options->pluginUrl);
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
<div class=\"dynamic-content\" itemprop=\"commentText\">
	<p>{text}</p>
</div>
</li>";
        }

        foreach ($dynamics as $dynamic) {
            $avatar = "https://gravatar.loli.net/avatar/" . md5($dynamic['mail']);
            $str .= str_replace(
                array('{did}', '{avatar}', '{authorId}', '{mail}', '{screenName}', '{text}', '{status}', '{created}', '{modified}', '{date}'),
                array($dynamic['did'], $avatar, $dynamic['authorId'], $dynamic['mail'], $dynamic['screenName'], $dynamic['text'], $dynamic['status'], $dynamic['created'], $dynamic['modified'], date($options->commentDateFormat, $dynamic['created'])),
                $pattern
            );
        }

        return $str;

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
