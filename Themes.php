<?php

use Typecho\Common;
use Typecho\Plugin as TypechoPlugin;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Submit;
use Typecho\Widget\Exception as WidgetException;
use TypechoPlugin\Dynamics\Option;

include 'common.php';
include 'header.php';
include 'menu.php';

$option = Option::alloc();

/**
 * @return bool
 */
function existConfig(): bool
{
    $option = Option::alloc();
    $configFile = $option->themesFile(
        $option->theme, 'functions.php'
    );

    if (file_exists($configFile)) {
        require_once $configFile;
        if (function_exists('_themeConfig')) {
            return true;
        }
    }
    return false;
}

if ($request->action == 'config'):
    if (!existConfig()) {
        throw new WidgetException(
            _t('外观配置功能不存在'), 404
        );
    }
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
                    <?php $form = new Form(
                        $security->getIndex('/action/dynamics?do=configTheme'), Form::POST_METHOD
                    );
                    _themeConfig($form);
                    $inputs = $form->getInputs();

                    $themeConfig = $options->plugin('Dynamics')->themeConfig;
                    $configs = unserialize($themeConfig);
                    if (!empty($inputs)) {
                        foreach ($inputs as $key => $val) {
                            $form->getInput($key)->value($configs[$key]);
                        }
                    }

                    $submit = new Submit(NULL, NULL, _t('保存设置'));
                    $submit->input->setAttribute('class', 'btn primary');
                    $form->addItem($submit);
                    $form->render(); ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($request->action == 'editor'): ?>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php';
            $_currentTheme = $request->filter('slug')->get('theme', $option->theme);
            if (preg_match("/^([_0-9a-z-\.\ ])+$/i", $_currentTheme)
                && is_dir($dir = $option->themesFile($_currentTheme))) {

                $_files = array_filter(glob($dir . '/*'), function ($path) {
                    return preg_match("/\.(php|js|css|vbs)$/i", $path);
                });

                $_currentFile = $request->get(
                    'file', 'index.php'
                );

                if (preg_match("/^([_0-9a-z-\.\ ])+$/i", $_currentFile)
                    && file_exists($dir . '/' . $_currentFile)) {
                    $files = array();
                    foreach ($_files as $file) {
                        if (file_exists($file)) {
                            $file = basename($file);
                            $files[] = array(
                                'file' => $file,
                                'theme' => $_currentTheme,
                                'current' => ($file == $_currentFile)
                            );
                        }
                    }
                }
            }
            if (empty($files)) {
                throw new WidgetException(
                    '风格文件不存在', 404
                );
            }
            ?>
            <div class="row typecho-page-main" role="main">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs fix-tabs clearfix">
                        <li>
                            <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php'); ?>"><?php _e('可用的动态主题'); ?></a>
                        </li>
                        <li class="current"><a
                                    href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor'); ?>">
                                <?php if ($options->theme == $_currentTheme): ?>
                                    <?php _e('编辑当前动态主题'); ?>
                                <?php else: ?>
                                    <?php _e('编辑%s动态主题', ' <cite>' . $_currentTheme . '</cite> '); ?>
                                <?php endif; ?>
                            </a></li>
                        <?php if (existConfig()): ?>
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
                            <textarea name="content" id="content" class="w-100 mono" <?php
                            $currentIsWriteable = is_writeable($option->themesFile($_currentTheme, $_currentFile));
                            if (!$currentIsWriteable): ?>readonly<?php endif; ?>><?= htmlspecialchars(file_get_contents(
                                    $option->themesFile($_currentTheme, $_currentFile)
                                )); ?></textarea>
                            <p class="submit">
                                <?php if ($currentIsWriteable): ?>
                                    <input type="hidden" name="theme" value="<?= $_currentTheme ?>"/>
                                    <input type="hidden" name="edit" value="<?= $_currentFile ?>"/>
                                    <button type="submit" class="btn primary"><?php _e('保存文件'); ?></button>
                                <?php else: ?>
                                    <em><?php _e('此文件无法写入'); ?></em>
                                <?php endif; ?>
                            </p>
                        </form>
                    </div>
                    <ul class="col-mb-12 col-tb-4 col-3">
                        <li><strong>模板文件</strong></li>
                        <?php foreach ($files as $file): ?>
                            <li<?php if ($file['current']): ?> class="current"<?php endif; ?>>
                                <a href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor&theme=' . $files['theme'] . '&file=' . $file['file']); ?>"><?= $file['file']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
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
                        <?php if (existConfig()): ?>
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
                            <?php $result = array();
                            if ($themes = glob($option->themesFile . '*', GLOB_ONLYDIR)) {
                                $activated = -1;
                                foreach ($themes as $key => $theme) {
                                    $themeFile = $theme . '/index.php';
                                    if (file_exists($themeFile)) {
                                        $info = TypechoPlugin::parseInfo($themeFile);
                                        $info['name'] = basename($theme);
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
                                            $info['screen'] = Common::url('noscreen.png', $options->adminStaticUrl('img'));
                                        }
                                        $result[$key] = $info;
                                    }
                                    unset($theme);
                                }

                                if ($activated >= 0) {
                                    $clone = $result[$activated];
                                    unset($result[$activated]);
                                    array_unshift($result, $clone);
                                }
                            }
                            foreach ($result as $theme): ?>
                                <tr id="theme-<?= $theme['name']; ?>"
                                    class="<?php if ($theme['activated']): ?>current<?php endif; ?>">
                                    <td valign="top"><img src="<?= $theme['screen']; ?>"
                                                          alt="<?= $theme['name']; ?>"/></td>
                                    <td valign="top">
                                        <h3><?= '' != $theme['title'] ? $theme['title'] : $theme['name']; ?></h3>
                                        <cite>
                                            <?php if ($theme['author']): ?><?php _e('作者'); ?>: <?php if ($theme['homepage']): ?><a href="<?= $theme['homepage'] ?>"><?php endif; ?><?= $theme['author']; ?><?php if ($theme['homepage']): ?></a><?php endif; ?> &nbsp;&nbsp;<?php endif; ?>
                                            <?php if ($theme['version']): ?><?php _e('版本'); ?>: <?= $theme['version']; ?><?php endif; ?>
                                        </cite>
                                        <p><?= nl2br($theme['description']); ?></p>
                                        <?php if ($option->theme != $theme['name']): ?>
                                            <p>
                                                <?php if (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__): ?>
                                                    <a class="edit"
                                                       href="<?php $options->adminUrl('extending.php?panel=Dynamics%2FThemes.php&action=editor&theme=' . $theme['name']); ?>"><?php _e('编辑'); ?></a> &nbsp;
                                                <?php endif; ?>
                                                <a class="activate"
                                                   href="<?php $security->index('/action/dynamics?do=changeTheme&change=' . $theme['name']); ?>"><?php _e('启用'); ?></a>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
