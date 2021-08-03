<?php

class Dynamics_Action extends Dynamics_Abstract implements Widget_Interface_Do
{

    /**
     * 同步附件
     *
     * @access protected
     * @param $did
     * @param $text
     * @return void
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     * @throws Typecho_Plugin_Exception
     */
    public static function attachOf($did, $text)
    {
        $option = Helper::options()->plugin('Dynamics');
        if (empty($did) || empty($option->archiveId)) {
            return;
        }
        $db = Typecho_Db::get();
        if (empty($db->fetchRow($db->select('cid')->from('table.contents')
            ->where('cid = ? AND type != ?', $option->archiveId, 'attachment')))) {
            return;
        }

        Typecho_Widget::widget('Widget_Contents_Attachment_Unattached')->to($attach);
        $order = 0;
        while ($attach->next()) {
            if (strpos($text, $attach->attachment->url) !== false) {
                $content = unserialize($attach->attachment->__toString());
                $content['dynamic'] = $did;
                unset($content['url'], $content['isImage']);

                $db->query($db->update('table.contents')
                    ->rows(array('text' => serialize($content), 'parent' => $option->archiveId, 'order' => ++$order))
                    ->where('cid = ? AND type = ?', $attach->cid, 'attachment')
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
     * @param integer $did 内容id
     * @return void
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     * @throws Typecho_Plugin_Exception
     */
    public static function unAttachOf($did)
    {
        $option = Helper::options()->plugin('Dynamics');
        if (empty($did) || empty($option->archiveId)) {
            return;
        }

        $db = Typecho_Db::get();
        Typecho_Widget::widget('Widget_Contents_Attachment_Related', 'parentId=' . $option->archiveId)->to($attach);

        while ($attach->next()) {
            if ($did == $attach->attachment->dynamic) {
                $content = unserialize($attach->attachment->__toString());
                unset($content['dynamic'], $content['url'], $content['isImage']);
                $db->query($db->update('table.contents')
                    ->rows(array('text' => serialize($content), 'parent' => 0))
                    ->where('cid = ? AND type = ?', $attach->cid, 'attachment'));
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
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function insertOf($uid, $dynamic)
    {
        $db = Typecho_Db::get();
        $dynamic['authorId'] = $uid;
        $dynamic['agent'] = $_SERVER['HTTP_USER_AGENT'];
        $dynamic['did'] = $db->query($db
            ->insert('table.dynamics')
            ->rows($dynamic));
        self::attachOf($dynamic['did'], $dynamic['text']);
        return $dynamic;
    }

    /**
     * 编辑
     *
     * @param $uid
     * @param $dynamic
     * @return mixed
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function modifyOf($uid, $dynamic)
    {
        $db = Typecho_Db::get();
        $dynamic['authorId'] = $uid;
        $db->query($db
            ->update('table.dynamics')
            ->rows($dynamic)
            ->where('did = ?', $dynamic['did']));
        self::attachOf($dynamic['did'], $dynamic['text']);
        return $dynamic;
    }

    /**
     * 删除
     *
     * @param $uid
     * @param $list
     * @return int
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     * @throws Typecho_Plugin_Exception
     */
    public static function deleteOf($uid, $list)
    {
        $db = Typecho_Db::get();
        $deleteCount = 0;
        foreach ($list as $did) {
            if ($db->query($db->delete('table.dynamics')
                ->where('authorId', $uid)->where('did = ?', $did))) {
                $deleteCount++;
            }
            self::unAttachOf($did);
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
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function selectOf($uid, $status = null, $pageSize = 10, $currentPage = 1)
    {
        $db = Typecho_Db::get();
        $select = $db->select()->from('table.dynamics')
            ->where('authorId = ?', $uid);

        if (isset($status) && $status != 'total') {
            $select->where('status = ?', $status);
        }

        $select->order('created', Typecho_Db::SORT_DESC)
            ->page($currentPage, $pageSize);

        $dynamicRough = $db->fetchAll($select);

        $list = [];
        $option = Typecho_Widget::widget('Dynamics_Option');

        foreach ($dynamicRough as $dynamic) {
            $dynamic['title'] = date('m月d日, Y年', $dynamic['created']);
            $dynamic['permalink'] = $option->applyUrl($dynamic['did']);
            $list[] = $dynamic;
        }

        return $list;
    }

    /**
     * 新增
     * @throws Typecho_Db_Exception|Typecho_Exception
     */
    public function addOf()
    {
        if (!$this->widget('Widget_User')->hasLogin()) {
            $this->error('请登录后台后重试');
        }

        $dynamic['text'] = '滴滴打卡';
        $dynamic['created'] = $date = time();
        $dynamic['modified'] = $date;

        $dynamic = Dynamics_Action::insertOf($this->user->uid, $dynamic);
        $dynamic['nickname'] = $this->user->screenName;

        $this->success($this->filterParam($dynamic));
    }

    /**
     * 保存
     *
     * @throws Typecho_Db_Exception|Typecho_Exception
     */
    public function saveOf()
    {
        if (!$this->widget('Widget_User')->hasLogin()) {
            $this->error('请登录后台后重试');
        }

        $dynamic = array(
            'did' => $this->request->get('did', 0),
            'text' => $this->request->get('text', '')
        );

        $this->success($this->filterParam(
            self::modifyOf($this->user->uid, $dynamic)
        ));
    }

    /**
     * 列表
     * @throws Typecho_Exception
     */
    public function listOf()
    {
        if (!$this->widget('Widget_User')->hasLogin()) {
            $this->error('请登录后台后重试');
        }
        $lid = $this->request->get('lastDid', 0);
        $size = 10;
        $select = $this->db->select('table.dynamics.*, table.users.screenName as nickname')
            ->from('table.dynamics')
            ->join('table.users', 'table.dynamics.authorId = table.users.uid')
            ->where('uid = ?', $this->user->uid);
        if ($lid) {
            $select->where('table.dynamics.did < ? ', $lid);
        }
        $select->order('table.dynamics.did', Typecho_Db::SORT_DESC)->limit($size);
        $data = $this->db->fetchAll($select);

        $dynamics = [];
        foreach ($data as $dynamic) {
            $dynamics[] = $this->filterParam($dynamic);
        }

        $this->success($dynamics);
    }

    /**
     * 删除
     * @throws Typecho_Exception
     */
    public function removeOf()
    {
        if (!$this->widget('Widget_User')->hasLogin()) {
            $this->error('请登录后台后重试');
        }
        $did = $this->request->get('did', 0);
        if (empty($did)) {
            $this->error('动态不存在');
        }

        if (self::deleteOf($this->user->uid, [$did])) {
            $this->success();
        } else {
            $this->error('没有可以删除的动态');
        }
    }

    /**
     * @param $theme
     * @throws Typecho_Exception
     * @throws Typecho_Plugin_Exception
     */
    public function changeTheme($theme)
    {
        $options = $this->options->plugin("Dynamics");
        $settings = [];
        foreach ($options as $key => $val) {
            $settings[$key] = $val;
        }
        $settings['theme'] = $theme;
        $settings['themeConfig'] = Dynamics_Plugin::changeTheme($theme);
        Helper::configPlugin('Dynamics', $settings);

        $this->widget('Widget_Notice')->set(_t("动态主题已经改变"), NULL, 'success');
        $this->response->goBack();
    }

    /**
     * 编辑外观文件
     *
     * @access public
     * @param string $theme 外观名称
     * @param string $file 文件名
     * @return void
     * @throws Typecho_Exception
     * @throws Typecho_Widget_Exception
     */
    public function editorTheme($theme, $file)
    {
        $option = Typecho_Widget::widget('Dynamics_Option');
        $path = $option->themesFile($theme, $file);

        if (file_exists($path) && is_writeable($path) && !Typecho_Common::isAppEngine()
            && (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__)) {
            $handle = fopen($path, 'wb');
            if ($handle && fwrite($handle, $this->request->content)) {
                fclose($handle);
                $this->widget('Widget_Notice')->set(_t("文件 %s 的更改已经保存", $file), 'success');
            } else {
                $this->widget('Widget_Notice')->set(_t("文件 %s 无法被写入", $file), 'error');
            }
            $this->response->goBack();
        } else {
            throw new Typecho_Widget_Exception(_t('您编辑的文件不存在'));
        }
    }

    /**
     * 配置外观
     *
     * @access public
     * @return void
     * @throws Typecho_Exception
     */
    public function configTheme()
    {
        $option = Typecho_Widget::widget('Dynamics_Option');
        $configFile = $option->themesFile($option->theme, 'functions.php');

        $isExists = false;
        if (file_exists($configFile)) {
            require_once $configFile;
            if (function_exists('_themeConfig')) {
                $isExists = true;
            }
        }

        if (!$isExists) {
            throw new Typecho_Widget_Exception(_t('外观配置功能不存在'), 404);
        }

        // 已经载入了外观函数
        $form = new Typecho_Widget_Helper_Form(NULL, Typecho_Widget_Helper_Form::POST_METHOD);
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
        Helper::configPlugin('Dynamics', $settings);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t("动态主题设置已经保存"), 'success');
        /** 转向原页 */
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
     * @throws Typecho_Exception
     */
    private function filterParam($dynamic)
    {
        $statusName = '';
        if ($dynamic["status"] == 'private') {
            $statusName = '[私密] ';
        } else if ($dynamic["status"] == 'hidden') {
            $statusName = '[隐藏] ';
        }

        $option = Typecho_Widget::widget('Dynamics_Option');

        $dynamic['title'] = $statusName . date('m月d日, Y年', $dynamic['created']);
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
        $this->on($this->request->is('do=add'))->addOf();
        $this->on($this->request->is('do=save'))->saveOf();
        $this->on($this->request->is('do=list'))->listOf();
        $this->on($this->request->is('do=remove'))->removeOf();

        $this->on($this->request->is('do=changeTheme'))->changeTheme($this->request->filter('slug')->change);
        $this->on($this->request->is('do=editorTheme'))
            ->editorTheme($this->request->filter('slug')->theme, $this->request->edit);
        $this->on($this->request->is('do=configTheme'))->configTheme();
    }
}
