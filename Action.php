<?php
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpIncludeInspection */
/** @noinspection DuplicatedCode */
include_once 'Dynamics_Abstract.php';

class Dynamics_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public $db;
    public $options;
    public $user;
    public $config;
    public $dynamic;
    public $dynamics;
    public $thisIs;
    public $slug;

    private $_params;
    private $_themeDir;

    /**
     * Dynamics_Action constructor.
     * @param $request
     * @param $response
     * @param null $params
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
        $this->options = Typecho_Widget::widget('Widget_Options');
        $this->config = $this->options->Plugin('Dynamics');
        $this->user = Typecho_Widget::widget('Widget_User');

        $this->_params = array(
            "pageSize" => $this->config->pageSize,
            "isPjax" => $this->config->isPjax == 1
        );
        $this->_themeDir = rtrim($this->options->themeFile($this->options->theme), '/') . '/';

    }

    /**
     * 动态首页
     * @param string $path
     * @param bool $isReturn
     * @return string
     */
    public function homeUrl($path = "", $isReturn = false)
    {
        call_user_func("Dynamics_Plugin::homeUrl", $path, $isReturn);
    }

    /**
     * 动态主题路径
     * @param string $path
     * @param bool $isReturn
     * @return string
     */
    public function themeUrl($path = "", $isReturn = false)
    {
        call_user_func("Dynamics_Plugin::themeUrl", $path, $isReturn);
    }

    /**
     * @Deprecated 已弃用, 暂时保留
     * @param string $path
     * @param bool $isReturn
     * @Deprecated
     */
    public function themeDirUrl($path = "", $isReturn = false)
    {
        call_user_func("Dynamics_Plugin::themeUrl", $path, $isReturn);
    }

    /**
     * 动态主题名字
     * @return string
     */
    public function themeName()
    {
        call_user_func("Dynamics_Plugin::themeName");
    }

    /**
     * 动态主题绝对路径
     * @param string $path
     * @return string
     */
    public static function themeFile($path = "")
    {
        call_user_func("Dynamics_Plugin::themeFile", $path);
    }

    /**
     * 动态首页
     */
    public function dispatchIndex()
    {
        $this->thisIs = "index";
        $this->import("functions.php");
        $this->dynamics = Dynamics_Plugin::get($this->_params);

        /* 引入布局 */
        $this->import('index.php');
    }

    /**
     * 路由分发
     */
    public function dispatch()
    {
        $this->thisIs = "post";
        $this->slug = $this->request->slug;
        $this->import("functions.php");

        $did = Dynamics_Plugin::parseUrl($this->slug);
        if (empty($did)) {
            $this->error404();
        }

        $select = $this->db->select(
            'table.dynamics.*',
            'table.users.screenName',
            'table.users.mail')
            ->from('table.dynamics')
            ->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN)
            ->where("table.dynamics.did = ?", $did);

        $dic = $this->db->fetchRow($select);
        if (count($dic) == 0) {
            $this->error404();
        }

        $this->dynamic = new Dynamics_Abstract(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $this->dynamic->setDid($dic['did']);
        $this->dynamic->setStatus($dic['status']);
        $this->dynamic->setAuthorId($dic['authorId']);
        $this->dynamic->setMail($dic['mail']);
        $this->dynamic->setAuthorName($dic['screenName']);
        $this->dynamic->setText($dic['text']);
        $this->dynamic->setCreated($dic['created']);
        $this->dynamic->setModified($dic['modified']);
        $this->dynamic->setAvatar($dic['avatar']);
        $this->dynamic->setAgent($dic['agent']);

        /* 引入布局 */
        $this->import('post.php');
    }

    /**
     * 当前位置，类似博客主题的 $this->is();
     * 首页 index
     * 动态 post
     * 404  404
     * @param $type
     * @return bool
     */
    public function thisIs($type)
    {
        return $this->thisIs == $type;
    }

    /**
     * 引入博客主题的文件
     * @param $fileName
     */
    public function need($fileName)
    {
        $path = $this->_themeDir . $fileName;
        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * 引入动态主题的文件
     * @param $fileName
     */
    public function import($fileName)
    {
        $path = Dynamics_Plugin::themeFile($fileName);
        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * 展示分页
     */
    public function showPage()
    {
        $this->thisIs = "page";
        $this->import("functions.php");
        $this->dynamics = Dynamics_Plugin::get($this->_params);
        //$this->import('page.php');
        $options = $this->config;
        
        while ($this->dynamics->next()){
            $temple = $options->templateInHome;
            //$temple = str_replace(array('{{did}}','{{avatar}}','{{authorName}}','{{url}}','{{created}}','{{content}}'),array($this->dynamics->did,$this->dynamics->avatar,$this->dynamics->authorName,$this->dynamics->url,$this->dynamics->created,$this->dynamics->content()),$temple);
            $temple = str_replace(array('{{did}}','{{authorName}}','{{url}}','{{created}}','{{content}}','{{avatar}}','{{authorId}}','{{modified}}','{{status}}','{{text}}','{{cuttext}}'),array($this->dynamics->did,$this->dynamics->authorName,$this->dynamics->url,date($options->timeFormat, $this->dynamics->created),$this->dynamics->content,$this->dynamics->avatar,$this->dynamics->authorId,date($options->timeFormat, $this->dynamics->modified),$this->dynamics->status,$this->dynamics->text,$this->subtext(strip_tags($this->dynamics->content),$options->cutTextLength)),$temple);
            echo $temple;
            //var_dump($this->dynamics->avatar);
            //$options->templateInHome;
            //echo $this->dynamics->content();
        }

        if($options->isHomePagenavgator){
            $this->dynamics->navigator();
        }
    }

    /**
     * 404
     */
    public function error404()
    {
        $this->response->setStatus(404);
        $this->thisIs = "404";
        $this->import("functions.php");
        $this->import('404.php');
        exit;
    }

    private function error($message = '', $data = array())
    {
        $this->response->throwJson(array(
            'result' => false,
            'message' => $message,
            'data' => $data
        ));
    }

    private function success($data = array(), $message = '')
    {
        $this->response->throwJson(array(
            'result' => true,
            'message' => $message,
            'data' => $data
        ));
    }

    private function filterParam($dynamic)
    {
        $statusName = "";
        if ($dynamic["status"] == "private") {
            $statusName = "[私密] ";
        } else if ($dynamic["status"] == "hidden") {
            $statusName = "[隐藏] ";
        }

        $dynamic["title"] = $statusName . date("m月d日, Y年", $dynamic["created"]);
        $dynamic["url"] = Dynamics_Plugin::applyUrl($dynamic["did"], true);
        $dynamic["desc"] = mb_substr(strip_tags($dynamic["text"]), 0, 20, 'utf-8');
        return $dynamic;
    }

    private function filterParams($data)
    {
        $dynamics = array();
        foreach ($data as $dynamic) {
            $dynamics[] = $this->filterParam($dynamic);
        }
        return $dynamics;
    }

    public function adds()
    {
        if (!$this->isAdmin()) {
            $this->error('请登录后台后重试');
        }
        $date = (new Typecho_Date($this->options->gmtTime))->time();
        $dynamic['text'] = "滴滴打卡";
        $dynamic['authorId'] = Typecho_Cookie::get('__typecho_uid');
        $dynamic['modified'] = $date;
        $dynamic['created'] = $date;
        $dynamic['agent'] = $_SERVER['HTTP_USER_AGENT'];
        /** 插入数据 */
        $dynamicId = $this->db->query($this->db->insert('table.dynamics')->rows($dynamic));
        $data = $this->db->fetchRow($this->db->select('table.dynamics.*, table.users.screenName author_name')
            ->from('table.dynamics')
            ->join('table.users', 'table.dynamics.authorId = table.users.uid')
            ->where('table.dynamics.did =  ?', $dynamicId)
        );
        $this->success($this->filterParam($data));
    }

    public function saves()
    {
        if (!$this->isAdmin()) {
            $this->error('请登录后台后重试');
        }
        $dynamicId = $this->request->get('did', 0);
        $data = array(
            'text' => $this->request->get('text', ''),
        );
        $this->db->query($this->db->update('table.dynamics')->rows($data)->where('did = ?', $dynamicId));
        $data = $this->db->fetchRow($this->db->select('table.dynamics.*, table.users.screenName author_name')
            ->from('table.dynamics')
            ->join('table.users', 'table.dynamics.authorId = table.users.uid')
            ->where('table.dynamics.did =  ?', $dynamicId)
        );
        $this->success($this->filterParam($data));
    }

    public function lists()
    {
        if (!$this->isAdmin()) {
            $this->error('请登录后台后重试');
        }
        $lastid = $this->request->get('lastdid', 0);
        $size = 10;
        $select = $this->db->select('table.dynamics.*, table.users.screenName author_name')->from('table.dynamics')->join('table.users', 'table.dynamics.authorId = table.users.uid');
        if ($lastid) {
            $select->where('table.dynamics.did < ? ', $lastid);
        }
        $select->order('table.dynamics.did', Typecho_Db::SORT_DESC)->limit($size);
        $this->success($this->filterParams($this->db->fetchAll($select)));
    }

    public function deletes()
    {
        if (!$this->isAdmin()) {
            $this->error('请登录后台后重试');
        }
        $id = $this->request->get('did', 0);
        if (!$id) {
            $this->success();
        }
        $this->db->query($this->db->delete('table.dynamics')->where('did = ?', $id));
        $this->success();
    }

    /**
     * 判断是否是管理员登录状态
     *
     * @access public
     * @return bool
     */
    public function isAdmin()
    {
        return $this->user->pass('administrator', true);
    }

    public function action()
    {
        $this->on($this->request->is('do=adds'))->adds();
        $this->on($this->request->is('do=saves'))->saves();
        $this->on($this->request->is('do=lists'))->lists();
        $this->on($this->request->is('do=deletes'))->deletes();
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Dynamics%2Fmanage-dynamics.php', $this->options->adminUrl));
    }

    /**
     * 切割文本省略
     */
    public function subtext($text, $length)
    {
        if(mb_strlen($text, 'utf8') > $length) {
            return mb_substr($text, 0, $length, 'utf8').'...';
        } else {
            return $text;
        }
    }
}
