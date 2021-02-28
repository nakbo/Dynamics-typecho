<?php
include 'common.php';
include 'header.php';
include 'menu.php';

class Dynamics_Themes_Files extends Typecho_Widget
{
    /**
     * 当前风格
     *
     * @access private
     * @var string
     */
    private $_currentTheme;

    /**
     * 当前文件
     *
     * @access private
     * @var string
     */
    private $_currentFile;

    /**
     * 执行函数
     *
     * @access public
     * @return void
     * @throws Typecho_Exception
     * @throws Typecho_Widget_Exception
     */
    public function execute()
    {
        /** 管理员权限 */
        $this->widget('Widget_User')->pass('administrator');
        $option = $this->widget('Dynamics_Option');
        $this->_currentTheme = $this->request->filter('slug')->get('theme', $option->theme);

        if (preg_match("/^([_0-9a-z-\.\ ])+$/i", $this->_currentTheme)
            && is_dir($dir = $option->themesFile($this->_currentTheme))) {

            $files = array_filter(glob($dir . '/*'), function ($path) {
                return preg_match("/\.(php|js|css|vbs)$/i", $path);
            });

            $this->_currentFile = $this->request->get('file', 'index.php');

            if (preg_match("/^([_0-9a-z-\.\ ])+$/i", $this->_currentFile)
                && file_exists($dir . '/' . $this->_currentFile)) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $file = basename($file);
                        $this->push(array(
                            'file' => $file,
                            'theme' => $this->_currentTheme,
                            'current' => ($file == $this->_currentFile)
                        ));
                    }
                }

                return;
            }
        }

        throw new Typecho_Widget_Exception('风格文件不存在', 404);
    }

    /**
     * 获取菜单标题
     *
     * @access public
     * @return string
     */
    public function getMenuTitle()
    {
        return _t('编辑文件 %s', $this->_currentFile);
    }

    /**
     * 获取文件内容
     *
     * @access public
     * @return string
     * @throws Typecho_Exception
     */
    public function currentContent()
    {
        return htmlspecialchars(file_get_contents($this->widget('Dynamics_Option')
            ->themesFile($this->_currentTheme, $this->_currentFile)));
    }

    /**
     * 获取文件是否可读
     *
     * @access public
     * @return string
     * @throws Typecho_Exception
     */
    public function currentIsWriteable()
    {
        return is_writeable($this->widget('Dynamics_Option')
                ->themesFile($this->_currentTheme, $this->_currentFile)) && !Typecho_Common::isAppEngine()
            && (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__);
    }

    /**
     * 获取当前文件
     *
     * @access public
     * @return string
     */
    public function currentFile()
    {
        return $this->_currentFile;
    }

    /**
     * 获取当前风格
     *
     * @access public
     * @return string
     */
    public function currentTheme()
    {
        return $this->_currentTheme;
    }
}

class Dynamics_Themes_List extends Typecho_Widget
{
    /**
     * @return array
     * @throws Typecho_Exception
     */
    protected function getThemes()
    {
        return glob($this->widget('Dynamics_Option')->themesFile . '*', GLOB_ONLYDIR);
    }

    /**
     * get theme
     *
     * @param string $theme
     * @param mixed $index
     * @return string
     */
    protected function getTheme($theme, $index)
    {
        return basename($theme);
    }

    /**
     * 执行函数
     *
     * @access public
     * @return void
     * @throws Typecho_Exception
     */
    public function execute()
    {
        $themes = $this->getThemes();

        if ($themes) {
            $options = $this->widget('Widget_Options');
            $option = $this->widget('Dynamics_Option');
            $activated = -1;
            $result = [];

            foreach ($themes as $key => $theme) {
                $themeFile = $theme . '/index.php';
                if (file_exists($themeFile)) {
                    $info = Typecho_Plugin::parseInfo($themeFile);
                    $info['name'] = $this->getTheme($theme, $key);

                    if ($option->followPath && trim($info['dependence']) != 'Dynamics') {
                        continue;
                    }

                    if ($info['activated'] = ($option->theme == $info['name'])) {
                        $activated = $key;
                    }

                    $screen = array_filter(glob($theme . '/*'), function ($path) {
                        return preg_match("/screenshot\.(jpg|png|gif|bmp|jpeg)$/i", $path);
                    });

                    if ($screen) {
                        $info['screen'] = $option->themesUrl($info['name'], basename(current($screen)));
                    } else {
                        $info['screen'] = Typecho_Common::url('noscreen.png', $options->adminStaticUrl('img'));
                    }
                    $result[$key] = $info;
                }
            }

            if ($activated >= 0) {
                $clone = $result[$activated];
                unset($result[$activated]);
                array_unshift($result, $clone);
            }
            array_filter($result, array($this, 'push'));
        }
    }
}

class Dynamics_Themes_Config extends Widget_Abstract_Options
{
    /**
     * 绑定动作
     *
     * @access public
     * @return void
     * @throws Typecho_Exception
     * @throws Typecho_Widget_Exception
     */
    public function execute()
    {
        $this->user->pass('administrator');

        if (!self::isExists()) {
            throw new Typecho_Widget_Exception(_t('外观配置功能不存在'), 404);
        }
    }

    /**
     * 配置功能是否存在
     *
     * @access public
     * @return boolean
     * @throws Typecho_Exception
     */
    public static function isExists()
    {
        $option = Typecho_Widget::widget('Dynamics_Option');
        $configFile = $option->themesFile($option->theme, 'functions.php');

        if (file_exists($configFile)) {
            require_once $configFile;
            if (function_exists('themeConfig')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 配置外观
     *
     * @access public
     * @return Typecho_Widget_Helper_Form
     * @throws Typecho_Plugin_Exception
     */
    public function config()
    {
        $form = new Typecho_Widget_Helper_Form($this->security->getIndex('/action/dynamics?do=configTheme'),
            Typecho_Widget_Helper_Form::POST_METHOD);
        themeConfig($form);
        $inputs = $form->getInputs();

        $themeConfig = $this->options->plugin("Dynamics")->themeConfig;
        $configs = unserialize($themeConfig);
        if (!empty($inputs)) {
            foreach ($inputs as $key => $val) {
                $form->getInput($key)->value($configs{$key});
            }
        }

        $submit = new Typecho_Widget_Helper_Form_Element_Submit(NULL, NULL, _t('保存设置'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        return $form;
    }
}

if ($request->action == "config"): ?>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main" role="main">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs fix-tabs clearfix">
                        <li>
                            <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php'); ?>"><?php _e('可用的动态主题'); ?></a>
                        </li>
                        <?php if (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__): ?>
                            <li>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor'); ?>"><?php _e('编辑当前动态主题'); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="current"><a
                                    href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=config'); ?>"><?php _e('设置动态主题'); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="col-mb-12 col-tb-8 col-tb-offset-2" role="form">
                    <?php Typecho_Widget::widget('Dynamics_Themes_Config')->config()->render(); ?>
                </div>
            </div>
        </div>
    </div>
<?php
elseif ($request->action == "editor"):
    Typecho_Widget::widget('Dynamics_Themes_Files')->to($files);
    ?>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main" role="main">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs fix-tabs clearfix">
                        <li>
                            <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php'); ?>"><?php _e('可用的动态主题'); ?></a>
                        </li>
                        <li class="current"><a
                                    href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor'); ?>">
                                <?php if ($options->theme == $files->theme): ?>
                                    <?php _e('编辑当前动态主题'); ?>
                                <?php else: ?>
                                    <?php _e('编辑%s动态主题', ' <cite>' . $files->theme . '</cite> '); ?>
                                <?php endif; ?>
                            </a></li>
                        <?php if (Dynamics_Themes_Config::isExists()): ?>
                            <li>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=config'); ?>"><?php _e('设置动态主题'); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="typecho-edit-theme">
                    <div class="col-mb-12 col-tb-8 col-9 content">
                        <form method="post" name="theme" id="theme"
                              action="<?php $security->index('/action/dynamics?do=editorTheme'); ?>">
                            <label for="content" class="sr-only"><?php _e('编辑源码'); ?></label>
                            <textarea name="content" id="content" class="w-100 mono"
                                      <?php if (!$files->currentIsWriteable()): ?>readonly<?php endif; ?>><?php echo $files->currentContent(); ?></textarea>
                            <p class="submit">
                                <?php if ($files->currentIsWriteable()): ?>
                                    <input type="hidden" name="theme" value="<?php echo $files->currentTheme(); ?>"/>
                                    <input type="hidden" name="edit" value="<?php echo $files->currentFile(); ?>"/>
                                    <button type="submit" class="btn primary"><?php _e('保存文件'); ?></button>
                                <?php else: ?>
                                    <em><?php _e('此文件无法写入'); ?></em>
                                <?php endif; ?>
                            </p>
                        </form>
                    </div>
                    <ul class="col-mb-12 col-tb-4 col-3">
                        <li><strong>模板文件</strong></li>
                        <?php while ($files->next()): ?>
                            <li<?php if ($files->current): ?> class="current"<?php endif; ?>>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor&theme=' . $files->currentTheme() . '&file=' . $files->file); ?>"><?php $files->file(); ?></a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php
else:
    $option = Typecho_Widget::widget('Dynamics_Option');
    ?>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main" role="main">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs fix-tabs clearfix">
                        <li class="current"><a
                                    href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php'); ?>"><?php _e('可用的动态主题'); ?></a>
                        </li>
                        <?php if (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__): ?>
                            <li>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor'); ?>"><?php _e('编辑动态主题'); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (Dynamics_Themes_Config::isExists()): ?>
                            <li>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=config'); ?>"><?php _e('设置动态主题'); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table typecho-theme-list">
                            <colgroup>
                                <col width="35%"/>
                                <col/>
                            </colgroup>

                            <thead>
                            <th><?php _e('截图'); ?></th>
                            <th><?php _e('详情'); ?></th>
                            </thead>

                            <tbody>
                            <?php Typecho_Widget::widget('Dynamics_Themes_List')->to($themes); ?>
                            <?php while ($themes->next()): ?>
                                <tr id="theme-<?php $themes->name(); ?>"
                                    class="<?php if ($themes->activated): ?>current<?php endif; ?>">
                                    <td valign="top"><img src="<?php $themes->screen(); ?>"
                                                          alt="<?php $themes->name(); ?>"/></td>
                                    <td valign="top">
                                        <h3><?php '' != $themes->title ? $themes->title() : $themes->name(); ?></h3>
                                        <cite>
                                            <?php if ($themes->author): ?><?php _e('作者'); ?>: <?php if ($themes->homepage): ?><a href="<?php $themes->homepage() ?>"><?php endif; ?><?php $themes->author(); ?><?php if ($themes->homepage): ?></a><?php endif; ?> &nbsp;&nbsp;<?php endif; ?>
                                            <?php if ($themes->version): ?><?php _e('版本'); ?>: <?php $themes->version() ?><?php endif; ?>
                                        </cite>
                                        <p><?php echo nl2br($themes->description); ?></p>
                                        <?php if ($option->theme != $themes->name): ?>
                                            <p>
                                                <?php if (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__): ?>
                                                    <a class="edit"
                                                       href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor&theme=' . $themes->name); ?>"><?php _e('编辑'); ?></a> &nbsp;
                                                <?php endif; ?>
                                                <a class="activate"
                                                   href="<?php $security->index('/action/dynamics?do=changeTheme&change=' . $themes->name); ?>"><?php _e('启用'); ?></a>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
endif;

include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
include 'footer.php';
?>
