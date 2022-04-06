<?php

use Typecho\Widget\Helper\Form\Element\Text;
use TypechoPlugin\Dynamics\Option;

/**
 * 主题配置
 * @param $form
 */
function _themeConfig($form)
{
    $option = Option::alloc();
    $themeUrl = dirname($option->themesUrl) . '/themes/Circle/';

    $logoUrl = new Text(
        'logoUrl', NULL, $themeUrl . 'logo.png',
        _t('LOGO'), _t('这里填写动态头部LOGO地址')
    );
    $form->addInput($logoUrl);

    $copyright = new Text(
        'copyright', NULL, '南博网络科技工作室 版权所有',
        _t('版权信息'), _t('在这里填入版权信息, 用于侧栏显示')
    );
    $form->addInput($copyright);
}

/**
 * @param $html
 * @return string|string[]|null
 */
function dynamic_lazyload_filter($html)
{
    return preg_replace('/<img\s+src="([^"]+)"/is',
        '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="$1" class="lazyload"', $html
    );
}