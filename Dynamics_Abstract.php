<?php

class Dynamics_Abstract extends Widget_Abstract_Contents implements Widget_Interface_Do
{
    private $_did;
    public $did;
    public $authorId;
    public $authorName;
    public $mail;
    public $text;
    public $content;
    public $created;
    public $modified;
    public $status;

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

    public function avatar()
    {
        echo "https://gravatar.loli.net/avatar/" . md5($this->mail);
    }

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
    }

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
    }

    /**
     * @throws Typecho_Widget_Exception
     */
    public function contents()
    {
        $this->setContent(Markdown::convert(trim($this->text)));
        echo $this->user->pass("administrator", true) ? $this->content : ($this->status == "private" ? "这是一条私密动态" : $this->content);
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
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
    }

    /**
     * @inheritDoc
     */
    public function action()
    {
        $this->db = Typecho_Db::get();
        $this->options = Typecho_Widget::widget('Widget_Options');
    }
}