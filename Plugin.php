<?php

namespace TypechoPlugin\Dynamics;

use Exception;
use Typecho\Db;
use Typecho\Widget\Request;
use Typecho\Widget\Response;
use Typecho\Request as HttpRequest;
use Typecho\Response as HttpResponse;
use Typecho\Plugin as TypechoPlugin;
use Typecho\Plugin\PluginInterface;
use Typecho\Plugin\Exception as PluginException;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Hidden;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Submit;
use Widget\Options;
use Utils\Helper;

/**
 * 我的动态
 *
 * @package Dynamics
 * @author 南博工作室
 * @version 2.1.2
 * @link https://github.com/krait-team/Dynamics-typecho/graphs/contributors
 */
class Plugin implements PluginInterface
{
    /**
     * @var string 默认动态首页相对路径
     */
    const DEFAULT_ROUTE = '/dynamics/';

    /**
     * 激活插件
     * @return string
     * @throws PluginException
     * @throws Db\Exception
     */
    public static function activate(): string
    {
        if (basename(dirname(__FILE__)) != 'Dynamics') {
            throw new PluginException(_t('插件目录名必须为 Dynamics'));
        }

        $db = Db::get();
        $prefix = $db->getPrefix();
        $adapterName = $db->getAdapterName();

        if (strpos($adapterName, 'Mysql') !== false) {
            try {
                $columns = $db->fetchAll($db->query("SHOW COLUMNS FROM {$prefix}dynamics;"));
                $columns = array_map(function ($column) {
                    return $column['Field'];
                }, $columns);
                $alter = array(
                    'agent' => 'ALTER TABLE `' . $prefix . 'dynamics` ADD `agent` varchar(511) DEFAULT NULL;'
                );
                foreach ($alter as $column => $query) {
                    if (!in_array($column, $columns)) {
                        $db->query($query);
                    }
                }
            } catch (Exception $e) {
                $db->query('CREATE TABLE IF NOT EXISTS `' . $prefix . 'dynamics` (
                `did` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `authorId` int(11) DEFAULT NULL,
                `text` text,
                `status` varchar(16) DEFAULT "publish",
                `agent` varchar(511) DEFAULT NULL,
                `created` int(10)	 DEFAULT 0,
                `modified` int(10)  DEFAULT 0,
                PRIMARY KEY (`did`)
              )');
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
            throw new PluginException(_t('你的适配器为%s，目前只支持Mysql和SQLite', $adapterName));
        }

        TypechoPlugin::factory('Nabo_Dynamics')->insert = 'TypechoPlugin\Dynamics\Action::onInsert';
        TypechoPlugin::factory('Nabo_Dynamics')->modify = 'TypechoPlugin\Dynamics\Action::onModify';
        TypechoPlugin::factory('Nabo_Dynamics')->delete = 'TypechoPlugin\Dynamics\Action::onDelete';
        TypechoPlugin::factory('Nabo_Dynamics')->select = 'TypechoPlugin\Dynamics\Action::onSelect';
        TypechoPlugin::factory('Widget_Archive')->query = 'TypechoPlugin\Dynamics\Dynamic::onArchiveQuery';

        Helper::addPanel(3, 'Dynamics/Manage.php', '我的动态', '动态管理', 'editor');
        Helper::addPanel(1, 'Dynamics/Themes.php', '动态外观', '动态主题', 'administrator');
        Helper::addAction('dynamics', 'TypechoPlugin\Dynamics\Action');
        Helper::addRoute('dynamics-index', Plugin::DEFAULT_ROUTE, 'TypechoPlugin\Dynamics\Archive', 'index');
        Helper::addRoute('dynamics-route', Plugin::DEFAULT_ROUTE . '[slug].html', 'TypechoPlugin\Dynamics\Archive', 'post');

        return _t('动态插件已经激活');
    }

    /**
     * 禁用插件
     *
     * @return string
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function deactivate(): string
    {
        if (Options::alloc()->plugin('Dynamics')->allowDrop) {
            $db = Db::get();
            $db->query("DROP TABLE `{$db->getPrefix()}dynamics`", Db::WRITE);
        }

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
     * @throws Db\Exception
     */
    public static function output()
    {
        $action = new Archive(
            new Request(
                HttpRequest::getInstance()
            ), new Response(
                HttpRequest::getInstance(),
                HttpResponse::getInstance()
            )
        );
        $action->page(true);
    }

    /**
     * 在主题中直接调用
     *
     * @return Archive
     * @throws Db\Exception
     */
    public static function get(): Archive
    {
        $action = new Archive(
            new Request(
                HttpRequest::getInstance()
            ), new Response(
                HttpRequest::getInstance(),
                HttpResponse::getInstance()
            )
        );
        $action->page();
        return $action;
    }

    /**
     * 插件配置面板
     *
     * @param Form $form
     */
    public static function config(Form $form)
    {
        $theme = new Hidden('theme');
        $form->addInput($theme);

        $themeConfig = new Hidden('themeConfig');
        $form->addInput($themeConfig);

        $radio = new Radio(
            'allowIndex', array(
            '0' => '关闭',
            '1' => '允许',
        ), '0', '首页插入动态', '启用后博客首页的文章按照时间顺序插入动态');
        $form->addInput($radio);

        $radio = new Text(
            'archiveId', null, 0,
            '附件归档', '这是动态附件归档的文章(cid), 请认真填写, 若为 0 表示不归档动态里的附件<br/>若动态里引用的文件未归档附件时候, 在<strong>动态发布时候</strong>将这些附件归档在这个文章里, 以防清理未规定附件时候被删掉.');
        $radio->input->setAttribute('class', 'mono w-35');
        $form->addInput($radio);

        $radio = new Radio(
            'followPath', array(
            '0' => '动态主题目录源',
            '1' => '博客主题目录源',
        ), '0', '动态主题源', '一般使用动态主题目录源  <a href="https://nabo.krait.cn/docs/#/course-dynamics?id=%E5%8A%A8%E6%80%81%E4%B8%BB%E9%A2%98%E6%BA%90" target="_blank">详情点击</a>');
        $form->addInput($radio);

        $btn = new Submit();
        $btn->input->setAttribute('class', 'btn');
        $btn->input->setAttribute('type', 'button');
        $btn->input->setAttribute('onclick', "javascrtpt:window.open('" . Helper::options()->adminUrl . "/extending.php?panel=Dynamics%2FThemes.php')");
        $form->addItem($btn);
        $btn->value(_t('设置动态外观'));

        $radio = new Text(
            'title', null, '我的动态',
            '动态标题', '这是动态页面的标题.');
        $form->addInput($radio);

        $radio = new Text(
            'pageSize', null, '5',
            '每页数目', '此数目用于动态首页每页显示的文章数目.');
        $form->addInput($radio);

        $radio = new Text(
            'avatarSize', null, '45',
            '头像图像大小', '调用不合适尺寸的图片会对前端加载速度造成影响，请按照自己的需求选择输出合适尺寸的图片<br>单位：px');
        $form->addInput($radio);

        $radio = new Text(
            'avatarPrefix', null, 'https://gravatar.loli.net/avatar/',
            '头像镜像节点', '当设置了全局镜像源时该镜像节点配置将失效');
        $form->addInput($radio);

        $radio = new Text(
            'avatarRandom', null, 'mm',
            '当无 Gravatar 头像时，使用的随机方案', '可以填些什么？可参照 <a href="https://en.gravatar.com/site/implement/images/#default-image" target="_blank">Gravatar 的官方说明</a>');
        $form->addInput($radio);

        $drop = new Radio(
            'allowDrop', array(
            '0' => '不删除',
            '1' => '删除',
        ), '0', '删数据表', '请选择是否在禁用插件时，删除我的动态的数据表，此表是本插件创建的。如果选择不删除，那么禁用后再次启用还是之前的用户数据就不用重新个人配置');
        $form->addInput($drop);

        $btn = new Submit();
        $btn->input->setAttribute('class', 'btn');
        $btn->input->setAttribute('type', 'button');
        $btn->input->setAttribute(
            'onclick', "javascrtpt:window.open('" . Options::alloc()->adminUrl . "/extending.php?panel=Dynamics%2FManage.php')"
        );
        $form->addItem($btn);
        $btn->value(_t('管理动态'));
    }

    /**
     * @param $settings
     * @return string|null
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function configCheck($settings): ?string
    {
        $config = Options::alloc()->plugin('Dynamics');
        if ($settings['archiveId'] != $config->archiveId) {
            $db = Db::get();
            if (empty($db->fetchRow($db->select('cid')->from('table.contents')
                ->where('cid = ? AND type != ?', $settings['archiveId'], 'attachment')))) {
                return "提交的附件归档文章不存在 [cid={$settings['archiveId']}]";
            }
        }
        return null;
    }

    /**
     * @param $settings
     * @param $isInit
     * @throws PluginException
     */
    public static function configHandle($settings, $isInit)
    {
        if ($isInit) {
            $themeName = 'Circle';
            $settings['theme'] = $themeName;
            $settings['themeConfig'] = self::changeTheme($themeName);
        } else {
            $config = Options::alloc()->plugin('Dynamics');
            $settings['theme'] = $config->theme;
            $settings['themeConfig'] = $config->themeConfig;
        }

        Helper::configPlugin(
            'Dynamics', $settings
        );
    }

    /**
     * @param $theme
     * @return false|string
     */
    public static function changeTheme($theme)
    {
        $option = Option::alloc();
        $configTemp = [];
        if (is_dir($option->themesFile($theme))) {
            $configFile = $option->themesFile(
                $theme, 'functions.php'
            );
            if (file_exists($configFile)) {
                require_once $configFile;
                if (function_exists('_themeConfig')) {
                    $form = new Form();
                    _themeConfig($form);
                    $configTemp = $form->getValues() ?: [];
                }
            }
        }
        return serialize($configTemp);
    }

    /**
     * @param null $action
     */
    public static function form($action = NULL)
    {
    }

    /**
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }
}
