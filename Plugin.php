<?php
include_once 'Dynamics.php';

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 权那他
 * @version 1.7
 * @link https://github.com/krait-team/Dynamics-typecho
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{
    /** 动态首页路径 */
    const DYNAMICS_ROUTE = '/dynamics/';
    private static $instance;
    private static $homeUrl;
    private static $themeBaseUrl;
    private static $themeName;

    /**
     * 激活插件
     * @return string|void
     * @throws Typecho_Db_Exception
     */
    public static function activate()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        if ($db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}dynamics';"))) {
            /* 表更新 解决老版本南博动态插件没有UA键值的问题 */
            $rows = $db->fetchRow($db->select()->from('table.dynamics'));
            $alter = array(
                "agent" => 'ALTER TABLE `' . $prefix . 'dynamics` ADD `agent` varchar(511) DEFAULT NULL;',
            );
            foreach ($alter as $column => $query) {
                if (!array_key_exists($column, $rows)) {
                    $db->query($query);
                }
            }
        } else {
            /* 从未安装过南博动态插件 进行完整的建表 */
            $db->query('CREATE TABLE IF NOT EXISTS `' . $prefix . 'dynamics` (
                `did` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `authorId` int(11) DEFAULT NULL,
                `text` text,
                `status` varchar(16) DEFAULT "publish",
                `created` int(10)	 DEFAULT 0,
                `modified` int(10)  DEFAULT 0,
                `agent` varchar(511) DEFAULT NULL,
                PRIMARY KEY (`did`)
              ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        }

        //ALTER TABLE `typecho_dynamics` ADD `ua` TEXT NULL DEFAULT NULL AFTER `modified`;
        Helper::addPanel(3, 'Dynamics/manage-dynamics.php', '我的动态', '我的动态列表', 'administrator');
        Helper::addAction('dynamics-manage', 'Dynamics_Action');
        Helper::addRoute('dynamics-index-route', Dynamics_Plugin::DYNAMICS_ROUTE, 'Dynamics_Action', 'dispatchIndex');
        Helper::addRoute('dynamics-route', Dynamics_Plugin::DYNAMICS_ROUTE . "[slug]/", 'Dynamics_Action', 'dispatch');

        return _t('插件已经激活');
    }

    /**
     * 禁用插件
     * @return string|void
     */
    public static function deactivate()
    {
        Helper::removePanel(3, 'Dynamics/manage-dynamics.php');
        Helper::removeAction('dynamics-manage');
        Helper::removeRoute('dynamics-index-route');
        Helper::removeRoute('dynamics-route');
        return _t('插件已被禁用');
    }

    /**
     * 动态首页的路径
     * @param string $path
     * @param bool $isReturn
     * @return string
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public static function homeUrl($path = "", $isReturn = false)
    {
        if (self::$homeUrl == null) {
            self::$homeUrl = Typecho_Common::url(Dynamics_Plugin::DYNAMICS_ROUTE, Typecho_Widget::widget('Widget_Options')->index);
        }
        $url = self::$homeUrl . $path;
        if ($isReturn) {
            return $url;
        } else {
            echo $url;
        }
    }

    /**
     * 主题的路径
     * @param string $path
     * @param bool $isReturn
     * @return string
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public static function themeUrl($path = "", $isReturn = false)
    {
        if (self::$themeBaseUrl == null) {
            self::$themeBaseUrl = Typecho_Common::url('/Dynamics/themes/' . Dynamics_Plugin::themeName() . "/", Typecho_Widget::widget('Widget_Options')->pluginUrl);
        }
        $url = self::$themeBaseUrl . $path;
        if ($isReturn) {
            return $url;
        } else {
            echo $url;
        }
    }

    /**
     * @Deprecated 已弃用, 暂时保留
     * @param string $path
     * @param bool $isReturn
     */
    public static function themeDirUrl($path = "", $isReturn = false)
    {
        call_user_func("Dynamics_Plugin::themeUrl", $path, $isReturn);
    }

    /**
     * 动态主题名字
     * @return mixed
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function themeName()
    {
        if (self::$themeName == null) {
            self::$themeName = Typecho_Widget::widget('Widget_Options')->Plugin('Dynamics')->theme;
        }
        return self::$themeName;
    }

    /**
     * 动态主题绝对路径
     * @param string $path
     * @return string
     */
    public static function themeFile($path = "")
    {
        return dirname(__FILE__) . "/themes/" . Dynamics_Plugin::themeName() . "/" . $path;
    }

    /**
     * @Deprecated 已弃用, 暂时保留
     * @param string $path
     * @return string
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public static function themeDir($path = "")
    {
        call_user_func("Dynamics_Plugin::themeFile", $path);
    }

    /**
     * 根据did 计算动态的链接
     * @param $did
     * @param bool $isReturn
     * @return string
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public static function applyUrl($did, $isReturn = false)
    {
        $slug = base64_encode($did);
        $slug = str_replace('=', '', $slug) . '=';
        if ($isReturn) {
            return self::homeUrl($slug, true);
        } else {
            self::homeUrl($slug, false);
        }
    }

    /**
     * 根据 slug 反解 did
     * @param $slug
     * @return int|string|null
     */
    public static function parseUrl($slug)
    {
        $did = base64_decode($slug);
        return intval($did) > 0 ? $did : NULL;
    }

    /**
     * 在主题中直接调用
     *
     * @access public
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function output()
    {
        $args = func_get_args();
        $action = new Dynamics_Action(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $action->showPage();
    }

    /**
     * 动态实例
     * @param array $params
     * @return Dynamics
     */
    public static function get($params = array())
    {
        $dynamics = new Dynamics(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $dynamics->parse($params);
        return $dynamics;
    }

    /**
     * 动态单实例
     * @param array $params
     * @return Dynamics
     */
    public static function getInstance($params = array())
    {
        if (self::$instance == null) {
            self::$instance = self::get($params);
        }
        return self::$instance;
    }

    /**
     * 获取动态主题名字列表
     * @return array
     */
    public static function getList()
    {
        $list = array();
        $themes = glob(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Dynamics/themes/*', GLOB_ONLYDIR);
        if ($themes) {
            foreach ($themes as $key => $theme) {
                $themeFile = $theme . '/index.php';
                if (file_exists($themeFile)) {
                    $list[basename($theme)] = basename($theme);
                }
            }
        }
        return $list;
    }

    /**
     * 插件配置面板
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
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
            'timeFormat', null, 'n\月j\日,Y  H:i:s',
            '动态日期格式', '');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarRandomString', null, 'mm',
            '当无 Gravatar 头像时，使用的随机方案', '可以填些什么？可参照 <a href="https://en.gravatar.com/site/implement/images/#default-image" target="_blank">Gravatar 的官方说明</a>');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarSize', null, '45',
            '头像图像大小', '调用不合适尺寸的图片会对前端加载速度造成影响，请按照自己的需求选择输出合适尺寸的图片<br>单位：px');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'theme', self::getList(), 'AlphaPure', _t('模板选择'), "选择一个动态的主题");
        $form->addInput($radio);
    }

    /**
     * 个人用户配置面板
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
