<?php
/**
 * 功能模块
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function dynamicReplacer($dynamic){
    $content = $dynamic->content;
    $did = $dynamic->did;
    $pattern = '/<\s*img[\s\S]+?(?:src=[\'"]([\S\s]*?)[\'"]\s*|alt=[\'"]([\S\s]*?)[\'"]\s*|[a-z]+=[\'"][\S\s]*?[\'"]\s*)+[\s\S]*?>/';
    $replacement = '<figure><a href="$1" data-fancybox="img-'.base64_encode($did).'" no-pjax data-type="image" data-caption="$2" ><img src="$1" alt="$2" title="点击放大图片"></a><figcaption class="article-holder">$2</figcaption></figure>';
    $content = preg_replace($pattern, $replacement, $content);
    //$content = str_replace("<br>","", $content);
    return $content;
}
