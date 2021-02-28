<?php

class Dynamics_Abstract extends Typecho_Widget
{
    /**
     * @var Typecho_Db
     */
    protected $db;

    /**
     * @var Widget_Options
     */
    protected $options;

    /**
     * @var Dynamics_Option
     */
    protected $option;

    /**
     * @var Widget_User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $hasLogin;

    /**
     * @var array
     */
    private $_dynamics_list = [], $_position = 0, $_list_length = 0;

    /**
     * @var boolean
     */
    public $visualAble, $pageNavigator;

    /**
     * 构造器
     *
     * @param $request
     * @param $response
     * @param null $params
     * @throws Typecho_Exception
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
        $this->options = $this->widget('Widget_Options');
        $this->option = $this->widget('Dynamics_Option');
        $this->user = $this->widget('Widget_User');
        $this->hasLogin = $this->user->pass("administrator", true);
    }

    /**
     * @param $list
     * @param $pageNavigator
     */
    public function pushed(&$list, &$pageNavigator)
    {
        $this->_dynamics_list = &$list;
        $this->_position = 0;
        $this->_list_length = count($list);
        $this->pageNavigator = &$pageNavigator;
    }

    /**
     * @param $dic
     */
    public function pushing(&$dic)
    {
        $this->authorName = $dic['screenName'];
        unset($dic['screenName']);
        foreach ($dic as $key => $value) {
            $this->{$key} = $value;
        }
        $this->url = $this->option->applyUrl($this->did);
        $this->visualAble = $this->hasLogin || $this->status != 'private';
        $this->content = $this->visualAble ? Markdown::convert(trim($this->text)) : '';
    }

    /**
     * 遍历
     * @return array|bool
     */
    public function next()
    {
        if ($this->_list_length > $this->_position) {
            $dic = $this->_dynamics_list[$this->_position];
            $this->pushing($dic);
            $this->_position++;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 分页布局
     */
    public function navigator()
    {
        echo "<ol class=\"dynamics-page-navigator\">" . $this->pageNavigator->show() . "</ol>";
    }

    /**
     * 获取操作系统信息
     *
     */
    public function deviceOs()
    {
        echo $this->deviceParseOs();
    }

    /**
     * 获取操作系统信息
     *
     * @return string
     */
    public function deviceParseOs()
    {
        if (($agent = $this->agent) == NULL) {
            return "未知";
        }
        if (preg_match('/Android\s([^\s|;]+)/i', $agent, $regs)) {
            return 'Android ' . $regs[1];
        } else if (preg_match('/windows\snt\s(10\.0|6\.3|6\.2|6\.1|6\.0|5\.1|5|.)/i', $agent, $regs)) {
            return "Windows " . [
                    "10.0" => "10",
                    "6.3" => "8.1",
                    "6.2" => "8",
                    "6.1" => "7",
                    "6.0" => "Vista",
                    "5.1" => "XP",
                    "5" => "2000"
                ][$regs[1]];
        } else if (preg_match('/(iPad|ubuntu|linux|iPhone|macintosh|symbian|typecho)/i', $agent, $regs)) {
            return [
                "ipad" => "iPad",
                "ubuntu" => "Ubuntu",
                "linux" => "Linux",
                "iphone" => "iPhone",
                "macintosh" => "Mac OS",
                "unix" => "Unix",
                "symbian" => "SymbianOS",
                "typecho" => "Typecho.org"
            ][strtolower($regs[0])];
        }
        return '未知设备';
    }

    /**
     * 判断南博客户端
     *
     */
    public function deviceTag()
    {
        echo $this->deviceParseTag();
    }

    /**
     * 判断南博客户端
     *
     * @return mixed|string
     */
    public function deviceParseTag()
    {
        if ($this->agent == NULL) {
            //老版本南博并没有存储UA串
            return '南博 (旧版)';
        }
        if (preg_match('/Nabo\/([^\s|;]+)/i', $this->agent, $regs)) {
            return '南博 ' . $regs[1];
        }
        return $this->deviceParseInfo();
    }

    /**
     * 判断手机具体型号
     *
     */
    public function deviceInfo()
    {
        echo $this->deviceParseInfo();
    }

    /**
     * 判断手机具体型号
     *
     * @return mixed|string
     */
    public function deviceParseInfo()
    {
        if ($this->agent == NULL) {
            return "未知";
        }
        if (preg_match('/\(.*;\s(.*)\sBuild.*\)/i', $this->agent, $regs)) {
            return $regs[1];
        }
        return $this->deviceParseOs();
    }

    /**
     * 头像
     *
     * @param string $size
     * @param string $rating
     * @param string $default
     */
    public function avatar($size = null, $rating = null, $default = null)
    {
        echo Typecho_Common::gravatarUrl($this->mail, $size ?: $this->option->avatarSize, $rating ?: $this->options->commentsAvatarRating, $default ?: $this->option->avatarRandomString, $this->request->isSecure());
    }

    /**
     * 动态内容，经过markdown解析
     * @param string $more 私密模板
     */
    public function content($more = "<div class=\"hideContent\">这是一条私密动态</div>")
    {
        echo $this->visualAble ? $this->content : $more;
    }

    /**
     * 动态创建时间
     * @param string $timeFormat
     */
    public function date($timeFormat = 'n\月j\日,Y  H:i:s')
    {
        echo date($timeFormat, $this->created);
    }

    /**
     * 动态创建时间
     * @param string $timeFormat
     */
    public function created($timeFormat = 'n\月j\日,Y  H:i:s')
    {
        echo date($timeFormat, $this->created);
    }

    /**
     * 动态创建时间
     * @param string $timeFormat
     */
    public function modified($timeFormat = 'n\月j\日,Y  H:i:s')
    {
        echo date($timeFormat, $this->created);
    }
}
