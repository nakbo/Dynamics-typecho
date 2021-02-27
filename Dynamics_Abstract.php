<?php

class Dynamics_Abstract extends Widget_Abstract_Contents implements Widget_Interface_Do
{
    public $visualAble;
    public $did;
    public $authorId;
    public $authorName;
    public $mail;
    public $text;
    public $content;
    public $created;
    public $modified;
    public $status;
    public $url;
    public $avatar;
    public $agent;

    /**
     * @param mixed $authorId
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * 获取 User Agent
     */
    public function agent()
    {
        echo $this->agent;
    }

        /** 
     * 获取操作系统信息 
     */
    public function deviceOs($agent = null)
    {
        $agent = $agent != null ? $agent : $this->agent;
        $device_other = array("ipad"=>"iPad","ubuntu"=>"Ubuntu","linux"=>"Linux","iphone"=>"iPhone","macintosh"=>"Mac OS","unix"=>"Unix","symbian"=>"SymbianOS","typecho"=>"Typecho.org");
        if (preg_match('/Android\s([^\s|;]+)/i', $agent, $regs)) {
               $os = 'Android ' . $regs[1];
        } else if (preg_match('/win/i', $agent)) {
           $device_win=array("nt 6.0"=>"Windows Vista","nt 6.1"=>"Windows 7","nt 5.1"=>"Windows XP","nt 6.2"=>"Windows 8","nt 6.3"=>"Windows 8.1","nt 10.0"=>"Windows 10","nt 5"=>"Windows 2000");
          if(preg_match('/(nt 6.0|nt 6.1|nt 5.1|nt 6.2|nt 6.3|nt 10.0|nt 5)/i', $agent,$regs)){
               $os = $device_win[strtolower($regs[0])];
           }else{
               $os = 'Windows'; 
           }
        } else  if (preg_match('/(iPad|ubuntu|linux|iPhone|macintosh|symbian|Typecho)/i', $agent,$regs)) {
               $os = $device_other[strtolower($regs[0])];
        }else{
               $os = '未知设备';
        }
        return $os;                 
      }
    /**
     * 判断南博客户端
     */
    public function deviceTag($agent = null)
    {
        $agent = $agent != null ? $agent : $this->agent;
	    if (preg_match('/Kraitnabo\/([^\s|;]+)/i', $agent, $regs)) {
            $return = '南博 '. $regs[1];
        } else if($agent == NULL){
            //老版本南博并没有存储UA串
            $return = '南博 (旧版)';
        } else {
            $return = $this->deviceInfo($agent);
        }
 
        echo $return;
    }

    /**
     * 判断手机具体型号
     */
    public function deviceInfo($agent = null)
    {
        $agent = $agent != null ? $agent : $this->agent;
        //\\(.*;\\s(.*)\\sBuild.*\\)
	    if (preg_match('/\(.*;\s(.*)\sBuild.*\)/i', $agent, $regs)) {
            $return = $regs[1];
	    } else {
            $return = $this->deviceOs($agent);
        }
 
        return $return;
    }

    /**
     * 作者id
     */
    public function authorId()
    {
        echo $this->authorId;
    }

    /**
     * @param mixed $authorId
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * 作者名字
     */
    public function authorName()
    {
        echo $this->authorName;
    }

    /**
     * @param mixed $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * 作者邮箱
     */
    public function mail()
    {
        echo $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * 作者头像
     * @param int $size
     * @param string $rating
     * @param string $default
     */
    public function avatar($size = '', $rating = '', $default = '')
    {
        $this->action();
        $rating = $this->options->commentsAvatarRating;
        $default = $this->config->avatarRandomString;
        $size = $this->config->avatarSize;
        echo Typecho_Common::gravatarUrl($this->mail, $size, $rating, $default, $this->request->isSecure());
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($size = '', $rating = '', $default = '')
    {
        $this->action();
        $rating = $this->options->commentsAvatarRating;
        $default = $this->config->avatarRandomString;
        $size = $this->config->avatarSize;
        $this->avatar = Typecho_Common::gravatarUrl($this->mail, $size, $rating, $default, $this->request->isSecure());
    }

    /**
     * 动态id
     */
    public function did()
    {
        echo $this->did;
    }

    /**
     * @param mixed $did
     */
    public function setDid($did)
    {
        $this->did = $did;
        $this->url = Dynamics_Plugin::applyUrl($did, true);
    }

    /**
     * 动态内容，没有解析
     */
    public function text()
    {
        echo $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
        $this->setContent($this->visualAble ? Markdown::convert(trim($this->text)) : "");
    }

    /**
     * 动态内容，经过markdown解析
     * @param string $privateTemplate 私密模板
     */
    public function content($privateTemplate = "<div class=\"hideContent\">这是一条私密动态</div>")
    {
        echo $this->visualAble ? $this->content : $privateTemplate;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * 动态创建时间
     * @param string $format
     */
    public function created()
    {
        $this->action();
        echo date($this->config->timeFormat, $this->created);
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * 动态更新时间
     */
    public function modified()
    {
        $this->action();
        echo date($this->config->timeFormat, $this->modified);
    }

    /**
     * @param mixed $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * 动态状态
     */
    public function status()
    {
        echo $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        try {
            $hasLogin = $this->user->pass("administrator", true);
        } catch (Exception $e) {
            $hasLogin = false;
        }
        $this->visualAble = $hasLogin ? true : ($this->status == "private" ? false : true);
    }

    /**
     * 动态的页面链接
     */
    public function url()
    {
        echo $this->url;
    }

    /**
     * action
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public function action()
    {
        $this->db = Typecho_Db::get();
        $this->options = Typecho_Widget::widget('Widget_Options');
        $this->config = $this->options->Plugin('Dynamics');
    }
}
