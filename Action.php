<?php

namespace TypechoPlugin\Dynamics;

use Typecho\Db;
use Typecho\Plugin\Exception;
use Typecho\Plugin\Exception as PluginException;
use Typecho\Widget\Exception as WidgetException;
use Typecho\Widget\Helper\Form;
use Typecho\Widget;
use Widget\ActionInterface;
use Widget\Notice;
use Widget\Contents\Attachment\Related;
use Widget\Contents\Attachment\Unattached;
use Utils\Helper;

class Action extends Dynamic implements ActionInterface
{
    /**
     * 同步附件
     *
     * @access protected
     * @param $did
     * @param $text
     * @return void
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function onAttach($did, $text)
    {
        $option = Helper::options()->plugin('Dynamics');
        if (empty($did) || empty($option->archiveId)) {
            return;
        }
        $db = Db::get();

        $result = $db->fetchRow($db
            ->select('cid')
            ->from('table.contents')
            ->where('cid = ? AND type != ?', $option->archiveId, 'attachment')
        );
        if (empty($result)) return;

        $order = 0;
        $attach = Unattached::alloc();
        while ($attach->next()) {
            if (strpos($text, $attach->attachment->url) !== false) {
                $content = $attach->attachment->toArray();
                $content['dynamic'] = $did;
                unset($content['url'], $content['isImage']);

                $db->query($db
                    ->update('table.contents')->rows([
                        'text' => serialize($content),
                        'parent' => $option->archiveId,
                        'order' => ++$order
                    ])->where(
                        'cid = ? AND type = ?',
                        $attach->cid, 'attachment'
                    )
                );
                unset($content);
            }
        }
        unset($order);
    }

    /**
     * 取消附件关联
     *
     * @access protected
     * @param int $did
     * @return void
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function onUnAttach($did)
    {
        $option = Helper::options()->plugin('Dynamics');
        if (empty($did) || empty($option->archiveId)) {
            return;
        }

        $db = Db::get();
        $attach = Related::alloc(
            'parentId=' . $option->archiveId
        );

        while ($attach->next()) {
            if ($did == $attach->attachment->dynamic) {
                $content = $attach->attachment->toArray();
                unset($content['dynamic'], $content['url'], $content['isImage']);
                $db->query(
                    $db->update('table.contents')->rows([
                        'text' => serialize($content), 'parent' => 0
                    ])->where(
                        'cid = ? AND type = ?', $attach->cid, 'attachment'
                    )
                );
                unset($content);
            }
        }
    }

    /**
     * 插入
     *
     * @param $uid
     * @param $dynamic
     * @return mixed
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function onInsert($uid, $dynamic)
    {
        $db = Db::get();
        $dynamic['authorId'] = $uid;
        $dynamic['agent'] = $_SERVER['HTTP_USER_AGENT'];

        $dynamic['did'] = $db->query(
            $db->insert('table.dynamics')
                ->rows($dynamic)
        );

        self::onAttach(
            $dynamic['did'],
            $dynamic['text']
        );

        return $dynamic;
    }

    /**
     * 编辑
     *
     * @param $uid
     * @param $dynamic
     * @return mixed
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function onModify($uid, $dynamic)
    {
        $db = Db::get();
        $dynamic['authorId'] = $uid;

        $db->query(
            $db->update('table.dynamics')
                ->rows($dynamic)->where(
                    'did = ?', $dynamic['did']
                )
        );

        self::onAttach(
            $dynamic['did'],
            $dynamic['text']
        );

        return $dynamic;
    }

    /**
     * 删除
     *
     * @param $uid
     * @param $list
     * @return int
     * @throws Db\Exception
     * @throws PluginException
     */
    public static function onDelete($uid, $list): int
    {
        $db = Db::get();
        $deleteCount = 0;

        foreach ($list as $did) {
            if ($db->query($db->delete('table.dynamics')
                ->where('table.dynamics.did = ?', $did))) {
                $deleteCount++;
                self::onUnAttach($did);
            }
        }

        return $deleteCount;
    }

    /**
     * 列表
     *
     * @param $uid
     * @param null $status
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws Db\Exception
     */
    public static function onSelect($uid, $status = null, $pageSize = 10, $currentPage = 1): array
    {
        $db = Db::get();
        $select = $db->select()->from('table.dynamics')
            ->where('authorId = ?', $uid);

        if (isset($status) && $status != 'total') {
            $select->where('status = ?', $status);
        }

        $select->order('created', Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        $data = $db->fetchAll($select);
        $option = Option::alloc();

        $dynamics = [];
        foreach ($data as $dynamic) {
            $dynamic['title'] = date('m月d日, Y年', $dynamic['created']);
            $dynamic['permalink'] = $option->applyUrl($dynamic['did']);
            $dynamics[] = $dynamic;
        }

        return $dynamics;
    }

    /**
     * 新增
     *
     * @throws Db\Exception
     * @throws PluginException
     * @throws WidgetException
     */
    public function onAdd()
    {
        $this->user->pass('editor');
        $dynamic = [
            'text' => '滴滴打卡',
            'created' => $date = time(),
            'modified' => $date
        ];

        $dynamic = self::onInsert(
            $this->user->uid, $dynamic
        );
        $dynamic['nickname'] = $this->user->screenName;

        $this->success(
            $this->filterParam($dynamic)
        );
    }

    /**
     * 保存
     *
     * @throws Db\Exception
     * @throws PluginException
     * @throws WidgetException
     */
    public function onSave()
    {
        $this->user->pass('editor');
        $dynamic = [
            'did' => $this->request->get('did', 0),
            'text' => $this->request->get('text', '')
        ];

        $this->success(
            $this->filterParam(
                self::onModify(
                    $this->user->uid, $dynamic
                )
            )
        );
    }

    /**
     * 列表
     *
     * @throws Db\Exception
     * @throws Widget\Exception
     */
    public function onList()
    {
        $this->user->pass('editor');
        $select = $this->db->select('table.dynamics.*, table.users.screenName as nickname')
            ->from('table.dynamics')
            ->join('table.users', 'table.dynamics.authorId = table.users.uid');

        if ($lid = $this->request->get('lastDid', 0)) {
            $select->where('table.dynamics.did < ? ', $lid);
        }

        $select->order('table.dynamics.did', Db::SORT_DESC)->limit(10);
        $data = $this->db->fetchAll($select);

        $dynamics = [];
        foreach ($data as $dynamic) {
            $dynamics[] = $this->filterParam($dynamic);
        }

        $this->success($dynamics);
    }

    /**
     * 删除
     *
     * @throws Db\Exception
     * @throws Exception
     * @throws Widget\Exception
     */
    public function onRemove()
    {
        $this->user->pass('editor');

        if (empty($did = $this->request->get('did', 0))) {
            $this->error('动态不存在');
        }

        if (self::onDelete($this->user->uid, [$did])) {
            $this->success();
        } else {
            $this->error('没有可以删除的动态');
        }
    }

    /**
     * @param $theme
     * @throws Exception
     */
    public function changeTheme($theme)
    {
        $options = $this->options->plugin('Dynamics');
        $settings = [];
        foreach ($options as $key => $val) {
            $settings[$key] = $val;
        }
        $settings['theme'] = $theme;
        $settings['themeConfig'] = Plugin::changeTheme($theme);

        Helper::configPlugin(
            'Dynamics', $settings
        );
        Notice::alloc()->set(
            _t("动态主题已经改变"), NULL, 'success'
        );
        $this->response->goBack();
    }

    /**
     * 编辑外观文件
     *
     * @access public
     * @param string $theme 外观名称
     * @param string $file 文件名
     * @return void
     * @throws Exception
     */
    public function editorTheme(string $theme, string $file)
    {
        $option = Option::alloc();
        $path = $option->themesFile(
            $theme, $file
        );

        if (file_exists($path) && is_writeable($path) &&
            (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__)) {
            $handle = fopen($path, 'wb');
            if ($handle && fwrite($handle, $this->request->content)) {
                fclose($handle);
                Notice::alloc()->set(
                    _t("文件 %s 的更改已经保存", $file), 'success'
                );
            } else {
                Notice::alloc()->set(
                    _t("文件 %s 无法被写入", $file), 'error'
                );
            }
            $this->response->goBack();
        } else {
            throw new Exception(_t('您编辑的文件不存在'));
        }
    }

    /**
     * 配置外观
     *
     * @access public
     * @return void
     * @throws PluginException
     * @throws WidgetException
     */
    public function configTheme()
    {
        $option = Option::alloc();
        $configFile = $option->themesFile(
            $option->theme, 'functions.php'
        );

        $isExists = false;
        if (file_exists($configFile)) {
            require_once $configFile;
            if (function_exists('_themeConfig')) {
                $isExists = true;
            }
        }

        if (!$isExists) {
            throw new WidgetException(_t('外观配置功能不存在'), 404);
        }

        $form = new Form(
            NULL, Form::POST_METHOD
        );
        _themeConfig($form);

        /** 验证表单 */
        if ($form->validate()) {
            $this->response->goBack();
        }

        $config = $form->getAllRequest();

        $options = $this->options->plugin('Dynamics');
        $settings = [];
        foreach ($options as $key => $val) {
            $settings[$key] = $val;
        }
        $settings['theme'] = $option->theme;
        $settings['themeConfig'] = serialize($config);

        Helper::configPlugin(
            'Dynamics', $settings
        );
        Notice::alloc()->set(
            _t('动态主题设置已经保存'), 'success'
        );
        $this->response->goBack();
    }

    /**
     * @param string $message
     * @param array $data
     */
    private function error($message = '', $data = [])
    {
        $this->response->throwJson([
            'code' => 0, 'msg' => $message,
            'data' => $data
        ]);
    }

    /**
     * 前端返回成功
     *
     * @param array $data
     * @param string $message
     */
    private function success($data = [], $message = '')
    {
        $this->response->throwJson([
            'code' => 1, 'msg' => $message,
            'data' => $data
        ]);
    }

    /**
     * @param $dynamic
     * @return mixed
     */
    private function filterParam($dynamic)
    {
        $status = '';
        if ($dynamic["status"] == 'private') {
            $status = '[私密] ';
        } else if ($dynamic["status"] == 'hidden') {
            $status = '[隐藏] ';
        }

        $option = Option::alloc();

        $dynamic['title'] = $status . date('m月d日, Y年', $dynamic['created']);
        $dynamic['url'] = $option->applyUrl($dynamic['did']);
        $dynamic['desc'] = mb_substr(strip_tags($dynamic['text']),
            0, 20, 'utf-8'
        );

        return $dynamic;
    }

    /**
     * 行动
     */
    public function action()
    {
        $this->on($this->request->is('do=add'))->onAdd();
        $this->on($this->request->is('do=save'))->onSave();
        $this->on($this->request->is('do=list'))->onList();
        $this->on($this->request->is('do=remove'))->onRemove();

        $this->on($this->request->is('do=changeTheme'))->changeTheme($this->request->filter('slug')->change);
        $this->on($this->request->is('do=editorTheme'))->editorTheme($this->request->filter('slug')->theme, $this->request->edit);
        $this->on($this->request->is('do=configTheme'))->configTheme();
    }
}
