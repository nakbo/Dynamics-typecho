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
     * @var Widget_User
     */
    public $user;

    /**
     * @var Widget_Options
     */
    public $options;

    /**
     * @var Dynamics_Option
     */
    public $option;

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
        $this->dynamics->archive = &$this;

        $this->page = $this->request->get('paging', 1);
        $this->pageSize = $this->option->pageSize;

        // compatible with older versions
        $this->dynamic = &$this->dynamics;
    }

    /**
     * 近期动态
     *
     */
    private function select()
    {
        $this->db->fetchAll($this->dynamics->select()
            ->where('table.dynamics.status != ?', 'hidden')
            ->order('table.dynamics.created', Typecho_Db::SORT_DESC)
            ->page($this->page, $this->pageSize), [$this->dynamics, 'push']);
        $this->total = $this->dynamics->size($this->db->select()
            ->where('table.dynamics.status != ?', 'hidden')
        );
    }

    /**
     * 动态首页
     *
     */
    public function index()
    {
        $this->type = 'index';
        $this->import('functions.php');

        $this->select();

        $this->import('index.php');
    }

    /**
     * 单个动态
     *
     */
    public function post()
    {
        $this->type = 'post';
        $this->slug = $this->request->slug;
        $this->import('functions.php');

        $did = $this->option->parseUrl($this->slug);
        if (empty($did)) {
            $this->error404();
        }

        $this->db->fetchAll($this->dynamics->select()
            ->where("table.dynamics.did = ?", $did), [$this->dynamics, 'push']);

        if ($this->error404) {
            $this->error404();
        }
        $this->title = date("m月d日, Y年", $this->dynamics->created);
        $this->description = mb_substr(strip_tags($this->dynamics->content), 0, 200, 'utf8');

        $this->import('post.php');
    }

    /**
     * 展示分页
     * @param bool $import
     */
    public function page($import = false)
    {
        $this->type = 'page';
        if ($import) {
            $this->import('functions.php');
        }

        $this->select();

        if ($import) {
            $this->import('page.php');
        }
    }

    /**
     * 404
     */
    public function error404()
    {
        $this->response->setStatus(404);
        $this->type = '404';
        $this->import('functions.php');
        $this->import('404.php');
        exit;
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
     * @param string $prev
     * @param string $next
     * @param int $splitPage
     * @param string $splitWord
     * @param string $template
     * @throws Typecho_Widget_Exception
     */
    public function pageNav($prev = '&laquo;', $next = '&raquo;', $splitPage = 3, $splitWord = '...', $template = '')
    {
        if ($this->dynamics->have()) {
            $default = array(
                'wrapTag' => 'ol',
                'wrapClass' => 'dynamics-page-navigator'
            );

            if (is_string($template)) {
                parse_str($template, $config);
            } else {
                $config = $template;
            }

            $template = array_merge($default, $config);

            if ($this->total > $this->pageSize) {
                $query = Typecho_Request::getInstance()->makeUriByRequest('paging={page}');

                /** 使用盒状分页 */
                $nav = new Typecho_Widget_Helper_PageNavigator_Box($this->total,
                    $this->page, $this->pageSize, $query);

                echo '<' . $template['wrapTag'] . (empty($template['wrapClass'])
                        ? '' : ' class="' . $template['wrapClass'] . '"') . '>';
                $nav->render($prev, $next, $splitPage, $splitWord, $template);
                echo '</' . $template['wrapTag'] . '>';
            }
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
     *
     * @return mixed
     */
    public function ___keywords()
    {
        return $this->keywords ?: $this->options->keywords;
    }

    /**
     * 描述
     *
     * @return mixed
     */
    public function ___description()
    {
        return $this->description ?: $this->options->description;
    }
}
