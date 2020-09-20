<?php
/**
 * 功能模块
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function dynamicReplacer($content){
    $pattern = '/<\s*img[\s\S]+?(?:src=[\'"]([\S\s]*?)[\'"]\s*|alt=[\'"]([\S\s]*?)[\'"]\s*|[a-z]+=[\'"][\S\s]*?[\'"]\s*)+[\s\S]*?>/';
    $replacement = '<figure><a href="$1" data-fancybox="gallery" no-pjax data-type="image" data-caption="$2" ><img src="$1" alt="$2" title="点击放大图片"></a><figcaption class="article-holder">$2</figcaption></figure>';
    $content = preg_replace($pattern, $replacement, $content);
    //$content = str_replace("<br>","", $content);
    return $content;
}

/** 对邮箱类型判定，并调用QQ头像的实现【本主题未使用到】 */
function isqq($email)
{
    if ($email) {
        if (strpos($email, "@qq.com") !== false) {
            $email = str_replace('@qq.com', '', $email);
            if(is_numeric($email)){
            echo "//q1.qlogo.cn/g?b=qq&nk=" . $email . "&";
            }else{
                $mmail = $email.'@qq.com';
                $email = md5($mmail);
                echo "//cdn.v2ex.com/gravatar/" . $email . "?";
            }
            
        } else {
            $email = md5($email);
            echo "//cdn.v2ex.com/gravatar/" . $email . "?";
        }
    } else {
        echo "//cdn.v2ex.com/gravatar/null?";
    }
}