<?php

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 陆之岇,尚寂新
 * @version 2.0.1
 * @link https://github.com/krait-team/Dynamics-typecho
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{
    /** 动态首页路径 */
    const DYNAMICS_ROUTE = '/dynamics/';

    /**
     * 激活插件
     * @return string|void
     * @throws Typecho_Db_Exception
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $packageName = basename(dirname(__FILE__));
        if ($packageName != 'Dynamics') {
            throw new Typecho_Plugin_Exception(_t('插件目录名必须为 Dynamics'));
        }

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $adapterName = $db->getAdapterName();

        if (strpos($adapterName, 'Mysql') !== false) {
            if ($db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}dynamics';"))) {
                /* 表更新 */
                $rows = $db->fetchRow($db->select()->from('table.dynamics'));
                $alter = [
                    "agent" => 'ALTER TABLE `' . $prefix . 'dynamics` ADD `agent` varchar(511) DEFAULT NULL;'
                ];
                foreach ($alter as $column => $query) {
                    if (!array_key_exists($column, $rows)) {
                        $db->query($query);
                    }
                }
            } else {
                $db->query('CREATE TABLE IF NOT EXISTS `' . $prefix . 'dynamics` (
                `did` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `authorId` int(11) DEFAULT NULL,
                `text` text,
                `status` varchar(16) DEFAULT "publish",
                `agent` varchar(511) DEFAULT NULL,
                `created` int(10)	 DEFAULT 0,
                `modified` int(10)  DEFAULT 0,
                PRIMARY KEY (`did`)
              ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
            }
        } else if (strpos($adapterName, 'SQLite') !== false) {
            if (!$db->fetchRow($db->query("SELECT name FROM sqlite_master WHERE TYPE='table' AND name='{$prefix}dynamics';", Typecho_Db::READ))) {
                $db->query('CREATE TABLE ' . $prefix . 'dynamics ( 
                    "did" INTEGER NOT NULL PRIMARY KEY, 
		            "authorId" int(11) DEFAULT 0,
		            "text" text,
		            "status" varchar(16) DEFAULT "publish",
		            "agent" varchar(511) DEFAULT NULL,
		            "created" int(10) DEFAULT 0,
		            "modified" int(10) DEFAULT 0);');
            }
        } else {
            throw new Typecho_Plugin_Exception(_t('你的适配器为%s，目前只支持Mysql和SQLite', $adapterName));
        }

        Typecho_Plugin::factory('Nabo_Dynamics')->insert = ['Dynamics_Action', 'insertOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->modify = ['Dynamics_Action', 'modifyOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->delete = ['Dynamics_Action', 'deleteOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->select = ['Dynamics_Action', 'selectOf'];

        Helper::addPanel(3, 'Dynamics/Manage.php', '我的动态', '动态列表', 'administrator');
        Helper::addPanel(1, 'Dynamics/Themes.php', '动态外观', '动态主题列表', 'administrator');
        Helper::addAction('dynamics', 'Dynamics_Action');
        Helper::addRoute('dynamics-index', Dynamics_Plugin::DYNAMICS_ROUTE, 'Dynamics_Archive', 'index');
        Helper::addRoute('dynamics-route', Dynamics_Plugin::DYNAMICS_ROUTE . "[slug].html", 'Dynamics_Archive', 'dispatch');
        Helper::addRoute('dynamics-route', Dynamics_Plugin::DYNAMICS_ROUTE . "[slug].html", 'Dynamics_Archive', 'dispatch');

        return _t('动态插件已经激活');
    }

    /**
     * 禁用插件
     *
     * @return string|void
     */
    public static function deactivate()
    {
        Helper::removePanel(3, 'Dynamics/Manage.php');
        Helper::removePanel(1, 'Dynamics/Themes.php');
        Helper::removeAction('dynamics');
        Helper::removeRoute('dynamics-index');
        Helper::removeRoute('dynamics-route');
        return _t('动态插件已被禁用');
    }

    /**
     * 在主题中直接调用
     *
     * @access public
     * @throws Typecho_Exception
     */
    public static function output()
    {
        $action = new Dynamics_Archive(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $action->parsePage(true);
    }

    /**
     * 在主题中直接调用
     *
     * @return Dynamics_Archive
     * @throws Typecho_Exception
     */
    public static function get()
    {
        $action = new Dynamics_Archive(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $action->parsePage();
        return $action;
    }

    /**
     * 插件配置面板
     *
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $theme = new Typecho_Widget_Helper_Form_Element_Hidden('theme');
        $form->addInput($theme);

        $themeConfig = new Typecho_Widget_Helper_Form_Element_Hidden('themeConfig');
        $form->addInput($themeConfig);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'title', null, '我的动态',
            '动态标题', '这是动态页面的标题.');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'followPath', array(
            '0' => '动态主题目录源',
            '1' => '博客主题目录源',
        ), '0', '动态主题源', '动态主题目录源: 则使用在目录/usr/plugins/Dynamics/themes下的主题<br>博客主题目录源: 则使用在目录/usr/themes下的主题 (其中主题信息里的dependence为Dynamics才可被扫描)');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'pageSize', null, '5',
            '动态首页每页数目', '此数目用于动态首页每页显示的文章数目.');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'isPjax', array(
            '0' => '使用 JavaScript 进行跳转',
            '1' => '使用 HTML (a 标签)进行跳转',
        ), '1', '动态页面部分的链接跳转实现', '使用 HTML (a 标签) 方式进行跳转，有利于部分【动态主题】的 Pjax 实现');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarRandomString', null, 'mm',
            '当无 Gravatar 头像时，使用的随机方案', '可以填些什么？可参照 <a href="https://en.gravatar.com/site/implement/images/#default-image" target="_blank">Gravatar 的官方说明</a>');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarSize', null, '45',
            '头像图像大小', '调用不合适尺寸的图片会对前端加载速度造成影响，请按照自己的需求选择输出合适尺寸的图片<br>单位：px');
        $form->addInput($radio);
    }

    /**
     * @param $settings
     * @param $isInit
     * @throws Typecho_Exception
     */
    public static function configHandle($settings, $isInit)
    {
        if ($isInit) {
            $themeName = 'AlphaPure';
            $settings['theme'] = $themeName;
            $settings['themeConfig'] = Dynamics_Plugin::changeTheme($themeName);
        } else {
            $config = Helper::options()->plugin('Dynamics');
            $settings['theme'] = $config->theme;
            $settings['themeConfig'] = $config->themeConfig;
        }
        Helper::configPlugin('Dynamics', $settings);
    }

    /**
     * @param $theme
     * @return false|string
     * @throws Typecho_Exception
     */
    public static function changeTheme($theme)
    {
        $option = Typecho_Widget::widget('Dynamics_Option');
        $configTemp = [];
        if (is_dir($option->themesFile($theme))) {
            $configFile = $option->themesFile($theme, 'functions.php');
            if (file_exists($configFile)) {
                require_once $configFile;
                if (function_exists('themeConfig')) {
                    $form = new Typecho_Widget_Helper_Form();
                    themeConfig($form);
                    $configTemp = $form->getValues() ?: [];
                }
            }
        }
        return serialize($configTemp);
    }

    /**
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * @param null $action
     */
    public static function form($action = NULL)
    {
    }
}
