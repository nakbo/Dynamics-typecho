<?php

/**
 * 我的动态
 *
 * @package Dynamics
 * @author 南博工作室
 * @version 2.1.0
 * @link https://github.com/krait-team/Dynamics-typecho/graphs/contributors
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
        if (basename(dirname(__FILE__)) != 'Dynamics') {
            throw new Typecho_Plugin_Exception(_t('插件目录名必须为 Dynamics'));
        }

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $adapterName = $db->getAdapterName();

        if (strpos($adapterName, 'Mysql') !== false) {
            if ($db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}dynamics';"))) {
                if ($rows = $db->fetchRow($db->select()->from('table.dynamics'))) {
                    $alter = array(
                        'agent' => 'ALTER TABLE `' . $prefix . 'dynamics` ADD `agent` varchar(511) DEFAULT NULL;'
                    );
                    foreach ($alter as $column => $query) {
                        if (!array_key_exists($column, $rows)) {
                            $db->query($query);
                        }
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

        Typecho_Plugin::factory('Widget_Archive')->query = ['Dynamics_Plugin', 'archiveQuery'];

        Typecho_Plugin::factory('Nabo_Dynamics')->insert = ['Dynamics_Action', 'insertOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->modify = ['Dynamics_Action', 'modifyOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->delete = ['Dynamics_Action', 'deleteOf'];
        Typecho_Plugin::factory('Nabo_Dynamics')->select = ['Dynamics_Action', 'selectOf'];

        Helper::addPanel(3, 'Dynamics/Manage.php', '我的动态', '动态列表', 'administrator');
        Helper::addPanel(1, 'Dynamics/Themes.php', '动态外观', '动态主题列表', 'administrator');
        Helper::addAction('dynamics', 'Dynamics_Action');
        Helper::addRoute('dynamics-index', Dynamics_Plugin::DYNAMICS_ROUTE, 'Dynamics_Archive', 'index');
        Helper::addRoute('dynamics-route', Dynamics_Plugin::DYNAMICS_ROUTE . '[slug].html', 'Dynamics_Archive', 'post');

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
        $action->page(true);
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
        $action->page();
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

        $radio = new Typecho_Widget_Helper_Form_Element_Radio(
            'followPath', array(
            '0' => '动态主题目录源',
            '1' => '博客主题目录源',
        ), '0', '动态主题源', '一般使用动态主题目录源  <a href="https://nabo.krait.cn/docs/#/course-dynamics?id=%E5%8A%A8%E6%80%81%E4%B8%BB%E9%A2%98%E6%BA%90" target="_blank">详情点击</a>');
        $form->addInput($radio);

        $btn = new Typecho_Widget_Helper_Form_Element_Submit();
        $btn->input->setAttribute('class', 'btn');
        $btn->input->setAttribute('type', 'button');
        $btn->input->setAttribute('onclick', "javascrtpt:window.open('" . Helper::options()->adminUrl . "/extending.php?panel=Dynamics%2FThemes.php')");
        $form->addItem($btn);
        $btn->value(_t('设置动态外观'));

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'title', null, '我的动态',
            '动态标题', '这是动态页面的标题.');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'pageSize', null, '5',
            '每页数目', '此数目用于动态首页每页显示的文章数目.');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarRandomString', null, 'mm',
            '当无 Gravatar 头像时，使用的随机方案', '可以填些什么？可参照 <a href="https://en.gravatar.com/site/implement/images/#default-image" target="_blank">Gravatar 的官方说明</a>');
        $form->addInput($radio);

        $radio = new Typecho_Widget_Helper_Form_Element_Text(
            'avatarSize', null, '45',
            '头像图像大小', '调用不合适尺寸的图片会对前端加载速度造成影响，请按照自己的需求选择输出合适尺寸的图片<br>单位：px');
        $form->addInput($radio);

        $btn = new Typecho_Widget_Helper_Form_Element_Submit();
        $btn->input->setAttribute('class', 'btn');
        $btn->input->setAttribute('type', 'button');
        $btn->input->setAttribute('onclick', "javascrtpt:window.open('" . Helper::options()->adminUrl . "/extending.php?panel=Dynamics%2FManage.php')");
        $form->addItem($btn);
        $btn->value(_t('管理动态'));
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
                if (function_exists('_themeConfig')) {
                    $form = new Typecho_Widget_Helper_Form();
                    _themeConfig($form);
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

    /**
     * @param Widget_Archive $archive
     * @param Typecho_Db_Query $select
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function archiveQuery($archive, $select)
    {
        $db = Typecho_Db::get();
        if (strpos($archive->parameter->type, 'index') !== 0) {
            $db->fetchAll($select, [$archive, 'push']);
            return;
        }

        $dynamicNum = $db->fetchObject($db->select(array('COUNT(DISTINCT table.dynamics.did)' => 'num'))
            ->from('table.dynamics')
            ->where('table.dynamics.status = ?', 'publish')
            ->cleanAttribute('group'))->num;
        if (empty($dynamicNum)) {
            $db->fetchAll($select, [$archive, 'push']);
            return;
        }

        $dynamicSize = 5;
        $archive->parameter->pageSize += $dynamicSize;

        $article = $select->prepare($select);
        $dynamic = $db->select('table.dynamics.did as cid', 'null as title', 'null as slug', 'table.dynamics.created', 'table.dynamics.authorId',
            'table.dynamics.modified', "'dynamic' as type", 'table.dynamics.status', 'table.dynamics.text', '0 as commentsNum', '0 as order',
            'null as template', 'null as password', '0 as allowComment', '0 as allowPing', '0 as allowFeed', '0 as parent')
            ->from('table.dynamics')
            ->where('table.dynamics.status = ?', 'publish')
            ->order('table.dynamics.created', Typecho_Db::SORT_DESC)
            ->page(isset($archive->request->page) ? $archive->request->page : 1, $dynamicSize);
        $dynamic = $dynamic->prepare($dynamic);

        $articleNum = $db->fetchObject($archive->getCountSql()
            ->select(array('COUNT(DISTINCT table.contents.cid)' => 'num'))
            ->from('table.contents')
            ->cleanAttribute('group'))->num;
        $archive->setTotal($articleNum + $dynamicNum);

        $option = Typecho_Widget::widget('Dynamics_Option');
        $tags = array();
        $categories = array([
            'name' => '动态',
            'permalink' => $option->homepage
        ]);

        foreach ($db->fetchAll("($article) UNION ($dynamic) ORDER BY created DESC") as $value) {
            if ($value['type'] == 'dynamic') {
                $value['title'] = date('m月d日, Y年', $value['created']);
                $value['tags'] = &$tags;
                $value['categories'] = &$categories;
                $value['permalink'] = $option->applyUrl($value['cid']);
                $value['isMarkdown'] = true;

                $value['date'] = new Typecho_Date($value['created']);
                $value['year'] = $value['date']->year;
                $value['month'] = $value['date']->month;
                $value['day'] = $value['date']->day;

                $archive->length++;
                $archive->stack[] = $value;
            } else {
                $archive->push($value);
            }
        }
    }
}
