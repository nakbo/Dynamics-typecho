<?php

namespace TypechoPlugin\Dynamics;

use Typecho\Common;
use Typecho\Db;
use Typecho\Widget;
use Typecho\Db\Query;
use Typecho\Date;
use Widget\Options;
use Widget\User;
use Utils\Markdown;

/**
 * Class Dynamics
 * @package TypechoPlugin\Dynamics
 *
 * @property-read int did
 * @property-read int authorId
 * @property-read int created
 * @property-read int modified
 * @property-read string status
 * @property-read string text
 * @property-read string content
 * @property-read string|null agent
 * @property-read string nickname
 * @property-read string screenName
 * @property-read string authorName
 * @property-read string permalink
 * @property-read string deviceOs
 * @property-read string deviceInfo
 * @property-read string|null dateFormat
 *
 * @property-read string mail
 */
class Dynamic extends Widget
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var Archive
     */
    public $archive;

    /**
     * 构造器
     *
     * @param $request
     * @param $response
     * @param null $params
     * @throws Db\Exception
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->db = Db::get();
        $this->options = $this->widget('Widget_Options');
        $this->option = $this->widget('Dynamics_Option');
        $this->user = $this->widget('Widget_User');
    }

    /**
     * @return Query
     * @throws Db\Exception
     */
    public function select(): Query
    {
        return $this->db->select(
            'table.dynamics.*',
            'table.users.screenName',
            'table.users.mail')
            ->from('table.dynamics')
            ->join('table.users',
                'table.dynamics.authorId = table.users.uid', Db::LEFT_JOIN
            );
    }

    /**
     *
     * @access public
     * @param Query $condition 查询对象
     * @return integer
     * @throws Db\Exception
     */
    public function size(Query $condition): int
    {
        return $this->db->fetchObject($condition
            ->select(['COUNT(DISTINCT table.dynamics.did)' => 'num'])
            ->from('table.dynamics')
            ->cleanAttribute('group')
        )->num;
    }

    /**
     * @return string
     */
    public function ___nickname(): string
    {
        return $this->screenName;
    }

    /**
     * @return string
     */
    public function ___authorName(): string
    {
        return $this->screenName;
    }

    /**
     * @return string
     */
    public function ___permalink(): string
    {
        return $this->option->applyUrl($this->did);
    }

    /**
     * @return string
     */
    public function ___url(): string
    {
        return $this->option->applyUrl($this->did);
    }

    /**
     * @return string
     * @throws Db\Exception
     */
    public function ___content(): string
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
    public function ___deviceOs(): string
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
     * @return string
     */
    public function ___deviceTag(): string
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
     * @return string
     */
    public function ___deviceInfo(): string
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
     * @return string
     */
    public function ___dateFormat(): string
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
        echo Common::gravatarUrl($this->mail,
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
     */
    public function navigator($prev = '&laquo;', $next = '&raquo;', $splitPage = 3, $splitWord = '...', $template = '')
    {
        $this->archive->pageNav($prev, $next, $splitPage, $splitWord, $template);
    }

    /**
     * @param $archive
     * @param $article
     * @throws Db\Exception
     */
    public static function archiveQuery($archive, $article)
    {
        $db = Db::get();
        $db->fetchAll($article, [$archive, 'push']);
        if (strpos($archive->parameter->type, 'index') !== 0) {
            return;
        }

        $option = Widget::widget('Dynamics_Option');
        if (empty($option->allowIndex)) {
            return;
        }

        $user = Widget::widget('Widget_User');
        $dynamic = $db->select('table.dynamics.did as cid', 'null as title', 'null as slug', 'table.dynamics.created', 'table.dynamics.authorId',
            'table.dynamics.modified', "'dynamic' as type", 'table.dynamics.status', 'table.dynamics.text', '0 as commentsNum', '0 as order',
            'null as template', 'null as password', '0 as allowComment', '0 as allowPing', '0 as allowFeed', '0 as parent')
            ->from('table.dynamics');
        if ($user->hasLogin()) {
            $dynamic->where(
                'table.dynamics.status = ? OR (table.dynamics.status = ? AND table.dynamics.authorId = ?)',
                'publish', 'private', $user->uid);
        } else {
            $dynamic->where('table.dynamics.status = ?', 'publish');
        }

        $dynamicNum = $db->fetchObject((clone $dynamic)
            ->select(['COUNT(DISTINCT table.dynamics.did)' => 'num'])
            ->from('table.dynamics')
            ->cleanAttribute('group'))->num;
        if (empty($dynamicNum)) {
            return;
        }

        $articleNum = $archive->size($archive->getCountSql());
        $archive->setTotal($articleNum + $dynamicNum);

        $archive->parameter->pageSize += $dynamicSize = 5;
        $dynamics = $db->fetchAll($dynamic
            ->order('table.dynamics.created', Db::SORT_DESC)
            ->page($archive->getCurrentPage(), $dynamicSize)
        );

        foreach ($dynamics as $value) {
            $value['title'] = date('m月d日, Y年', $value['created']);
            $value['isMarkdown'] = true;
            $value['tags'] = [];
            $value['categories'] = array([
                'name' => '动态',
                'permalink' => $option->homepage
            ]);
            $value['date'] = new Date($value['created']);
            $value['permalink'] = $option->applyUrl($value['cid']);

            $archive->length++;
            $archive->stack[] = $value;
        }

        uasort($archive->stack, function ($pre, $next) {
            return $pre['created'] > $next['created'] ? -1 : 1;
        });
    }
}
