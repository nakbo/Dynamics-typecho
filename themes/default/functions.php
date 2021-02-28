<?php

/**
 * 主题配置
 * @param $form
 */
function themeConfig($form)
{
    // 头像图
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, "https://gravatar.loli.net/avatar/4e4559eceb7fbd4bca7925710592b1b9?s=100&r=G&d=mm", _t('头像图'), _t('这里填写 URL 地址,最好能走cdn或者oss,毕竟带宽小'));
    $form->addInput($logoUrl);

    // 你的名字
    $yourName = new Typecho_Widget_Helper_Form_Element_Text('yourName', NULL, "权那他", _t('你的名字'), _t('在左上角头向下面,太长会自动...省略'));
    $form->addInput($yourName);

    // 座右铭好
    $motto = new Typecho_Widget_Helper_Form_Element_Text('motto', NULL, "正在创作一切未来", _t('座右铭'), _t('座右铭会默认显示在首页'));
    $form->addInput($motto);

    $libCdn = new Typecho_Widget_Helper_Form_Element_Radio('libCdn', array(
        'https://cdnjs.loli.net/ajax/libs/' => 'cdnjs.loli 公共库 ( https://cdnjs.loli.net/ajax/libs/ )',
        'https://lib.baomitu.com/' => 'cdn.baomitu 公共库 ( https://lib.baomitu.com/ )',
        'https://cdnjs.cloudflare.com/ajax/libs/' => 'cdnjs.cloudflare 公共库 ( https://cdnjs.cloudflare.com/ajax/libs/ )',
        'https://cdn.bootcss.com/' => 'cdn.bootcss 公共库 ( https://cdn.bootcss.com/ )'),
        'https://cdnjs.loli.net/ajax/libs/', _t('选择公共库'), _t('替换选择需要的公共库'));
    $form->addInput($libCdn->multiMode());
}

function testFunction($msg)
{
    echo "testFunction:" . $msg;
}