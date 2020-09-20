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
    public function avatar($size = 200, $rating = 'X', $default = 'mm')
    {
        echo Typecho_Common::gravatarUrl($this->mail, $size, $rating, $default, $this->request->isSecure());
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
    public function created($format = "n\月j\日,Y  H:i:s")
    {
        echo date($format, $this->created);
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
        echo $this->modified;
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
    }
}