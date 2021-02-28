<?php

/**
 * 主题配置
 * @param $form
 */
function themeConfig($form)
{
    $radio = new Typecho_Widget_Helper_Form_Element_Text(
        'timeFormat', null, 'n\月j\日,Y  H:i:s',
        '动态日期格式', '');
    $form->addInput($radio);
}

function dynamicReplacer($dynamic)
{
    $content = $dynamic->content;
    $did = $dynamic->did;
    $pattern = '/<\s*img[\s\S]+?(?:src=[\'"]([\S\s]*?)[\'"]\s*|alt=[\'"]([\S\s]*?)[\'"]\s*|[a-z]+=[\'"][\S\s]*?[\'"]\s*)+[\s\S]*?>/';
    $replacement = '<figure><a href="$1" data-fancybox="img-' . base64_encode($did) . '" no-pjax data-type="image" data-caption="$2" ><img src="$1" alt="$2" title="点击放大图片"></a><figcaption class="article-holder">$2</figcaption></figure>';
    $content = preg_replace($pattern, $replacement, $content);
    //$content = str_replace("<br>","", $content);
    return $content;
}
