<?php
include_once 'Dynamics.php';

/**
 * 我的动态 - 南博助手
 * @package Dynamics
 * @author 权那他
 * @version 1.4
 * @link https://github.com/kraity/Dynamics
 */
class Dynamics_Plugin implements Typecho_Plugin_Interface
{
    /** 动态首页路径 */
    const DYNAMICS_ROUTE = '/dynamics/';
    private static $instance;
    public static $homeUrl;
    public static $themeDirUrl;
    public static $themeName;

    // 激活插件
    public static function activate()
    {
        Helper::addPanel(3, 'Dynamics/manage-dynamics.php', '我的动态', '我的动态列表', 'administrator');
        Helper::addAction('dynamics-manage', 'Dynamics_Action');
        Helper::addRoute('dynamics-index-route', Dynamics_Plugin::DYNAMICS_ROUTE, 'Dynamics_Action', 'dispatchIndex');
        Helper::addRoute('dynamics-route', Dynamics_Plugin::DYNAMICS_ROUTE . "[slug]/", 'Dynamics_Action', 'dispatch');

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
        Helper::removeRoute('dynamics-index-route');
        Helper::removeRoute('dynamics-route');
        return _t('插件已被禁用');
    }

    /**
     * 动态首页的路径
     * @param string $path
     * @param bool $isReturn
     * @return string
     */
    public static function homeUrl($path = "", $isReturn = false)
    {
        if (self::$homeUrl == null) {
            try {
                self::$homeUrl = Typecho_Common::url(Dynamics_Plugin::DYNAMICS_ROUTE, Typecho_Widget::widget('Widget_Options')->index);
            } catch (Typecho_Exception $e) {
            }
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
     */
    public static function themeDirUrl($path = "", $isReturn = false)
    {
        if (self::$themeDirUrl == null) {
            try {
                self::$themeDirUrl = Typecho_Common::url('/Dynamics/' . Dynamics_Plugin::themeName() . "/", Typecho_Widget::widget('Widget_Options')->pluginUrl);
            } catch (Typecho_Exception $e) {
            }
        }
        $url = self::$themeDirUrl . $path;
        if ($isReturn) {
            return $url;
        } else {
            echo $url;
        }
    }

    /**
     * 动态主题名字
     * @return string
     */
    public static function themeName()
    {
        if (self::$themeName == null) {
            try {
                self::$themeName = "themes/" . Typecho_Widget::widget('Widget_Options')->Plugin('Dynamics')->theme;
            } catch (Typecho_Exception $e) {
            }
        }
        return self::$themeName;
    }

    /**
     * 根据did 计算动态的链接
     * @param $did
     * @param bool $isReturn
     * @return string
     */
    public static function applyUrl($did, $isReturn = false)
    {
        if ($isReturn) {
            return self::homeUrl(base64_encode($did), true);
        } else {
            self::homeUrl(base64_encode($did), false);
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
     * @param null $pattern
     * @param int $num
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function output($pattern = NULL, $num = 5)
    {
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

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'pageSize', null, '5',
            '动态首页每页数目', '此数目用于动态首页每页显示的文章数目.');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'isPjax', array(
            '0' => '未启用',
            '1' => '已启用',
        ), '0', 'Pjax状态', '是否开启Pjax状态,未完善');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'theme', self::getList(), 'AlphaPure', _t('模板选择'), "选择一个动态的主题");
        $form->addInput($radio);
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function form($action = NULL)
    {
    }
}
