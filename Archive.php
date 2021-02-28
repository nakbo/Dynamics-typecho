<?php

class Dynamics_Archive extends Typecho_Widget
{
    /**
     * @var Dynamics_Abstract
     */
    public $dynamic, $dynamics;

    /**
     * @var Typecho_Db
     */
    protected $db;

    /**
     * @var Widget_Options
     */
    public $options;

    /**
     * @var Dynamics_Option
     */
    public $option;

    /**
     * @var Widget_User
     */
    public $user;

    public $slug, $type, $title, $description, $keywords, $page = 1;
    private $_params;
    public $error404 = false, $dynamicsNum;

    /**
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
        $this->dynamics = $this->widget('Dynamics_Abstract');
        // 兼容旧版本
        $this->dynamic = &$this->dynamics;
        $this->page = $this->request->get('dynamicsPage', 1);
    }

    /**
     * 动态首页
     *
     */
    public function index()
    {
        $this->type = "index";
        $this->import("functions.php");
        $this->parse($this->_params);

        /* 引入布局 */
        $this->import('index.php');
    }

    /**
     * 路由分发
     *
     */
    public function dispatch()
    {
        $this->type = "post";
        $this->slug = $this->request->slug;
        $this->import("functions.php");

        $did = $this->option->parseUrl($this->slug);
        if (empty($did)) {
            $this->error404();
        }
        $this->_params['did'] = $did;
        $this->parse($this->_params);

        if ($this->error404) {
            $this->error404();
        }
        $this->title = date("m月d日, Y年", $this->dynamics->created);
        $this->description = mb_substr(strip_tags($this->dynamics->content), 0, 200, 'utf8');
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
    public function is($type)
    {
        return $this->type == $type;
    }

    /**
     * 引入博客主题的文件
     *
     * @param $fileName
     */
    public function need($fileName)
    {
        if (file_exists($path = $this->option->_themeFile($fileName))) {
            require_once $path;
        }
    }

    /**
     * 引入动态主题的文件
     *
     * @param $fileName
     */
    public function import($fileName)
    {
        if (file_exists($path = $this->option->themeFile($fileName))) {
            require_once $path;
        }
    }

    /**
     * 输出归档标题
     *
     * @param mixed $defines
     * @param string $before
     * @param string $end
     * @access public
     * @return void
     */
    public function titleArchive($defines = NULL, $before = ' &raquo; ', $end = '')
    {
        if ($this->title) {
            $define = '%s';
            if (is_array($defines) && !empty($defines[$this->type])) {
                $define = $defines[$this->type];
            }
            echo $before . sprintf($define, $this->title) . $end;
        }
    }

    /**
     * 404
     */
    public function error404()
    {
        $this->response->setStatus(404);
        $this->type = "404";
        $this->import("functions.php");
        $this->import('404.php');
        exit;
    }

    /**
     * 构造器
     *
     * @param array $format
     */
    public function parse($format = [])
    {
        if (($did = (int)$format["did"]) > 0) {
            $select = $this->db->select(
                'table.dynamics.*',
                'table.users.screenName',
                'table.users.mail')
                ->from('table.dynamics')
                ->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN)
                ->where("table.dynamics.did = ?", $did);
            if (count($dic = $this->db->fetchRow($select)) == 0) {
                $this->error404 = true;
                return;
            }
            $this->dynamics->pushing($dic);
        } else {
            $pageSize = ($pageSize = (int)$format["pageSize"]) > 0 ? $pageSize : 5;
            $select = $this->db->select(
                'table.dynamics.*',
                'table.users.screenName',
                'table.users.mail')
                ->where("table.dynamics.status != ?", "hidden")
                ->from('table.dynamics');
            $select->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);

            $select = $select->order('table.dynamics.created', Typecho_Db::SORT_DESC);
            $select = $select->page($this->page, $pageSize);

            $list = $this->db->fetchAll($select);
            $this->dynamicsNum = $this->db->fetchRow($this->db->select('count(1) AS count')->from('table.dynamics'))['count'];
            $this->dynamics->pushed($list, new Dynamics_Page(
                $pageSize, $this->dynamicsNum, $this->page, 4, [
                    "isPjax" => (boolean)$format["isPjax"]
                ]
            ));
        }
    }

    /**
     * 展示分页
     * @param bool $import
     */
    public function parsePage($import = false)
    {
        $this->type = "page";
        if ($import) {
            $this->import("functions.php");
        }
        $this->parse($this->_params);
        if ($import) {
            $this->import('page.php');
        }
    }

    /**
     * header
     */
    public function header()
    {
        $this->pluginHandle()->header($this);
    }

    /**
     * single
     */
    public function single()
    {
        $this->pluginHandle()->single($this);
    }

    /**
     * footer
     */
    public function footer()
    {
        $this->pluginHandle()->footer($this);
    }

    /**
     * 关键词
     */
    public function keywords()
    {
        echo $this->keywords ?: $this->options->keywords;
    }

    /**
     * 描述
     */
    public function description()
    {
        echo $this->description ?: $this->options->description;
    }
}
