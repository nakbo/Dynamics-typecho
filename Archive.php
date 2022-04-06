<?php

namespace TypechoPlugin\Dynamics;

use Typecho\Db;
use Typecho\Request;
use Typecho\Widget;
use Typecho\Widget\Helper\PageNavigator\Box;
use Widget\User;
use Widget\Options;

/**
 * Class Archive
 * @package TypechoPlugin\Dynamics
 *
 * @property int total
 * @property int page
 * @property int pageSize
 * @property string type
 * @property string|null slug
 * @property string|null title
 * @property string|null description
 * @property string|null keywords
 */
class Archive extends Widget
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Option
     */
    public $option;

    /**
     * @var Options
     */
    public $options;

    /**
     * @var Dynamic
     */
    public $dynamic, $dynamics;

    /**
     * Widget init
     *
     * @throws Db\Exception
     */
    protected function init()
    {
        $this->db = Db::get();
        $this->user = User::alloc();
        $this->option = Option::alloc();
        $this->options = Options::alloc();

        $this->dynamics = Dynamic::alloc();
        $this->dynamics->archive = &$this;

        // compatible with older versions
        $this->dynamic = &$this->dynamics;

        $this->page = $this->request->get('paging', 1);
        $this->pageSize = $this->option->pageSize;
    }

    /**
     * 近期动态
     *
     * @throws Db\Exception
     */
    private function select()
    {
        $this->db->fetchAll(
            $this->dynamics->select()
                ->where('table.dynamics.status != ?', 'hidden')
                ->order('table.dynamics.created', Db::SORT_DESC)
                ->page($this->page, $this->pageSize),
            [$this->dynamics, 'push']
        );

        $this->total = $this->dynamics->size(
            $this->db->select()->where('table.dynamics.status != ?', 'hidden')
        );
    }

    /**
     * 动态首页
     *
     * @throws Db\Exception
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
     * @throws Db\Exception
     */
    public function post()
    {
        $this->type = 'post';
        $this->slug = $this->request->slug;
        $this->import('functions.php');

        $did = $this->option
            ->parseUrl($this->slug);
        if (empty($did)) {
            $this->error404();
        }

        $this->db->fetchAll(
            $this->dynamics->select()
                ->where('table.dynamics.did = ?', $did),
            [$this->dynamics, 'push']
        );

        if ($this->error404) {
            $this->error404();
        }

        $this->title = date(
            'm月d日, Y年', $this->dynamics->created
        );
        $this->description = mb_substr(
            strip_tags($this->dynamics->content),
            0, 200, 'utf8'
        );

        $this->import('post.php');
    }

    /**
     * 展示分页
     * @param bool $import
     * @throws Db\Exception
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
    public function is($type): bool
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
     * @throws Widget\Exception
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
                $query = Request::getInstance()->makeUriByRequest('paging={page}');

                /** 使用盒状分页 */
                $nav = new Box(
                    $this->total, $this->page, $this->pageSize, $query
                );

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
    public function ___keywords(): string
    {
        return $this->keywords ?: $this->options->keywords;
    }

    /**
     * 描述
     *
     * @return mixed
     */
    public function ___description(): string
    {
        return $this->description ?: $this->options->description;
    }
}
