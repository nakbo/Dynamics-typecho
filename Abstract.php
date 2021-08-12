<?php

class Dynamics_Abstract extends Typecho_Widget
{
    /**
     * @var Dynamics_Archive
     */
    public $archive;

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
    }

    /**
     * @return Typecho_Db_Query
     */
    public function select()
    {
        return $this->db->select(
            'table.dynamics.*',
            'table.users.screenName',
            'table.users.mail')
            ->from('table.dynamics')
            ->join('table.users',
                'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);
    }

    /**
     *
     * @access public
     * @param Typecho_Db_Query $condition 查询对象
     * @return integer
     */
    public function size(Typecho_Db_Query $condition)
    {
        return $this->db->fetchObject($condition
            ->select(array('COUNT(DISTINCT table.dynamics.did)' => 'num'))
            ->from('table.dynamics')
            ->cleanAttribute('group'))->num;
    }

    /**
     * @return string
     */
    public function ___nickname()
    {
        return $this->screenName;
    }

    /**
     * @return string
     */
    public function ___authorName()
    {
        return $this->screenName;
    }

    /**
     * @return string
     */
    public function ___permalink()
    {
        return $this->option->applyUrl($this->did);
    }

    /**
     * @return string
     */
    public function ___url()
    {
        return $this->option->applyUrl($this->did);
    }

    /**
     * @return string
     */
    public function ___content()
    {
        if ($this->status == 'private') {
            if (!$this->user->hasLogin() || $this->user->uid != $this->authorId) {
                return '<div class="hideContent">这是一条私密动态</div>';
            }
        }
        return Markdown::convert($this->text);
    }

    /**
     * 获取操作系统信息
     *
     * @return string
     */
    public function ___deviceOs()
    {
        if (($agent = $this->agent) == NULL) {
            return "未知";
        }
        if (preg_match('/Android\s([^\s|;]+)/i', $agent, $regs)) {
            return 'Android ' . $regs[1];
        } else if (preg_match('/windows\snt\s(10\.0|6\.3|6\.2|6\.1|6\.0|5\.1|5|.)/i', $agent, $regs)) {
            return 'Windows ' . [
                    '10.0' => '10',
                    '6.3' => '8.1',
                    '6.2' => '8',
                    '6.1' => '7',
                    '6.0' => 'Vista',
                    '5.1' => 'XP',
                    '5' => '2000'
                ][$regs[1]];
        } else if (preg_match('/(iPad|ubuntu|linux|iPhone|macintosh|symbian|typecho)/i', $agent, $regs)) {
            return [
                'ipad' => 'iPad',
                'ubuntu' => 'Ubuntu',
                'linux' => 'Linux',
                'iphone' => 'iPhone',
                'macintosh' => 'Mac OS',
                'unix' => 'Unix',
                'symbian' => 'SymbianOS',
                'typecho' => 'Typecho.org'
            ][strtolower($regs[0])];
        }
        return '未知设备';
    }

    /**
     * 判断南博客户端
     *
     * @return mixed|string
     */
    public function ___deviceTag()
    {
        if ($this->agent == NULL) {
            //老版本南博并没有存储UA串
            return '南博 (旧版)';
        }
        if (preg_match('/Nabo\/([^\s|;]+)/i', $this->agent, $regs)) {
            return '南博 ' . $regs[1];
        }
        return $this->deviceInfo;
    }

    /**
     * 判断手机具体型号
     *
     * @return mixed|string
     */
    public function ___deviceInfo()
    {
        if ($this->agent == NULL) {
            return "未知";
        }
        if (preg_match('/\(.*;\s(.*)\sBuild.*\)/i', $this->agent, $regs)) {
            return $regs[1];
        }
        return $this->deviceOs;
    }

    /**
     * @return mixed|string|null
     */
    public function ___dateFormat()
    {
        return empty($this->option->dateFormat) ? 'n\月j\日,Y  H:i:s' : $this->option->dateFormat;
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
        echo Typecho_Common::gravatarUrl($this->mail,
            $size ?: $this->option->avatarSize,
            $rating ?: $this->options->commentsAvatarRating,
            $default ?: $this->option->avatarRandomString,
            $this->request->isSecure()
        );
    }

    /**
     * 动态创建时间
     *
     * @param null $format
     */
    public function date($format = NULL)
    {
        $this->created($format);
    }

    /**
     * 动态创建时间
     *
     * @param null $format
     */
    public function created($format = NULL)
    {
        echo date(empty($format) ? $this->dateFormat : $format, $this->created);
    }

    /**
     * 动态更新时间
     *
     * @param null $format
     */
    public function modified($format = NULL)
    {
        echo date(empty($format) ? $this->dateFormat : $format, $this->modified);
    }

    /**
     * 分页布局
     *
     * @param string $prev
     * @param string $next
     * @param int $splitPage
     * @param string $splitWord
     * @param string $template
     * @throws Typecho_Widget_Exception
     */
    public function navigator($prev = '&laquo;', $next = '&raquo;', $splitPage = 3, $splitWord = '...', $template = '')
    {
        $this->archive->pageNav($prev, $next, $splitPage, $splitWord, $template);
    }

    /**
     * @param Widget_Archive $archive
     * @param Typecho_Db_Query $select
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function archiveQuery($archive, $select)
    {
        $db = Typecho_Db::get();
        if (strpos($archive->parameter->type, 'index') !== 0) {
            $db->fetchAll($select, [$archive, 'push']);
            return;
        }

        $option = Typecho_Widget::widget('Dynamics_Option');
        if (empty($option->allowIndex)) {
            $db->fetchAll($select, [$archive, 'push']);
            return;
        }

        $dynamicNum = $db->fetchObject($db->select(array('COUNT(DISTINCT table.dynamics.did)' => 'num'))
            ->from('table.dynamics')
            ->where('table.dynamics.status = ?', 'publish')
            ->cleanAttribute('group'))->num;
        if (empty($dynamicNum)) {
            $db->fetchAll($select, [$archive, 'push']);
            return;
        }

        $dynamicSize = 5;
        $archive->parameter->pageSize += $dynamicSize;

        $article = $select->prepare($select);
        $dynamic = $db->select('table.dynamics.did as cid', 'null as title', 'null as slug', 'table.dynamics.created', 'table.dynamics.authorId',
            'table.dynamics.modified', "'dynamic' as type", 'table.dynamics.status', 'table.dynamics.text', '0 as commentsNum', '0 as order',
            'null as template', 'null as password', '0 as allowComment', '0 as allowPing', '0 as allowFeed', '0 as parent')
            ->from('table.dynamics')
            ->where('table.dynamics.status = ?', 'publish')
            ->order('table.dynamics.created', Typecho_Db::SORT_DESC)
            ->page(isset($archive->request->page) ? $archive->request->page : 1, $dynamicSize);
        $dynamic = $dynamic->prepare($dynamic);

        $articleNum = $db->fetchObject($archive->getCountSql()
            ->select(array('COUNT(DISTINCT table.contents.cid)' => 'num'))
            ->from('table.contents')
            ->cleanAttribute('group'))->num;
        $archive->setTotal($articleNum + $dynamicNum);

        $tags = array();
        $categories = array([
            'name' => '动态',
            'permalink' => $option->homepage
        ]);

        foreach ($db->fetchAll("($article) UNION ($dynamic) ORDER BY created DESC") as $value) {
            if ($value['type'] == 'dynamic') {
                $value['title'] = date('m月d日, Y年', $value['created']);
                $value['tags'] = &$tags;
                $value['categories'] = &$categories;
                $value['permalink'] = $option->applyUrl($value['cid']);
                $value['isMarkdown'] = true;

                $value['date'] = new Typecho_Date($value['created']);
                $value['year'] = $value['date']->year;
                $value['month'] = $value['date']->month;
                $value['day'] = $value['date']->day;

                $archive->length++;
                $archive->stack[] = $value;
            } else {
                $archive->push($value);
            }
        }
    }
}
