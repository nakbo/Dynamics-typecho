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
    private $params;
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

        $this->params = array(
            "isPjax" => $this->config->isPjax == 1
        );

        /** 初始化主题皮肤路径 */
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
        if ($isReturn) {
            return Dynamics_Plugin::homeUrl($path, true);
        } else {
            Dynamics_Plugin::homeUrl($path);
        }
    }

    /**
     * 动态主题路径
     * @param string $path
     * @param bool $isReturn
     * @return string
     */
    public function themeDirUrl($path = "", $isReturn = false)
    {
        if ($isReturn) {
            return Dynamics_Plugin::themeDirUrl($path, true);
        } else {
            Dynamics_Plugin::themeDirUrl($path);
        }
    }

    /**
     * 动态主题名字
     * @return string
     */
    public function getThemeName()
    {
        return Dynamics_Plugin::themeName();
    }

    /**
     * 动态首页
     */
    public function dispatchIndex()
    {
        $this->thisIs = "index";
        $this->import("functions.php");
        $this->params['pageSize'] = $this->config->pageSize;
        $this->dynamics = Dynamics_Plugin::get($this->params);
        require_once Dynamics_Plugin::themeDir('index.php');
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
            $this->thisIs = "404";
            require_once Dynamics_Plugin::themeDir('404.php');
            exit;
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
            $this->thisIs = "404";
            require_once Dynamics_Plugin::themeDir('404.php');
            exit;
        }

        $dynamic = new Dynamics_Abstract(
            Typecho_Request::getInstance(),
            Typecho_Response::getInstance()
        );
        $dynamic->setDid($dic['did']);
        $dynamic->setStatus($dic['status']);
        $dynamic->setAuthorId($dic['authorId']);
        $dynamic->setMail($dic['mail']);
        $dynamic->setAuthorName($dic['screenName']);
        $dynamic->setText($dic['text']);
        $dynamic->setCreated($dic['created']);
        $dynamic->setModified($dic['modified']);
        $this->dynamic = $dynamic;

        require_once Dynamics_Plugin::themeDir('post.php');
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
        $path = Dynamics_Plugin::themeName() . DIRECTORY_SEPARATOR . $fileName;
        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * 展示分页
     */
    public function showPage()
    {
        $this->dynamics = Dynamics_Plugin::get($this->params);
        require Dynamics_Plugin::themeDir('page.php');
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
        $dynamic["title"] = ($dynamic["status"] == "private" ? "[私密] " : "") . date("m月d日, Y年", $dynamic["created"]);
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
}