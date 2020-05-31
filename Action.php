<?php

class Dynamics_Action extends Widget_Abstract_Contents implements Widget_Interface_Do
{

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

    private function filterParams($params = array())
    {
        $data = array();
        $data['text'] = isset($params['text']) ? $params['text'] : '';
        $data['authorId'] = isset($params['authorId']) ? $params['authorId'] : 0;

        if ($data['authorId'] <= 0) {
            $this->error('请登录后台后重试');
        }

        $date = (new Typecho_Date($this->options->gmtTime))->format('Y-m-d H:i:s');

        $data['created'] = $date;
        $data['modified'] = $date;
        return $data;
    }

    public function adds()
    {
        if (!$this->user->pass("administrator", true)) {
            $this->error('请登录后台后重试');
        }

        $date = (new Typecho_Date($this->options->gmtTime))->time();
        $dynamic['text'] = "滴滴打卡";
        $dynamic['authorId'] = $this->user->uid;
        $dynamic['modified'] = $date;

        $dynamic['created'] = $date;
        /** 插入数据 */
        $dynamicId = $this->db->query($this->db->insert('table.dynamics')->rows($dynamic));

        $data = $this->db->fetchRow($this->db->select('table.dynamics.*, table.users.screenName author_name')->from('table.dynamics')->join('table.users', 'table.dynamics.authorId = table.users.uid')->where('table.dynamics.did =  ?', $dynamicId));

        $this->success($data);
    }

    public function saves()
    {
        if (!$this->user->pass("administrator", true)) {
            $this->error('请登录后台后重试');
        }

        $dynamicId = $this->request->get('did', 0);

        $data = array(
            'text' => $this->request->get('text', ''),
        );

        $this->db->query($this->db->update('table.dynamics')->rows($data)->where('did = ?', $dynamicId));

        $data = $this->db->fetchRow($this->db->select('table.dynamics.*, table.users.screenName author_name')->from('table.dynamics')->join('table.users', 'table.dynamics.authorId = table.users.uid')->where('table.dynamics.did =  ?', $dynamicId));

        $this->success($data);
    }

    public function lists()
    {
        if (!$this->user->pass("administrator", true)) {
            $this->error('请登录后台后重试');
        }

        $lastid = $this->request->get('lastdid', 0);
        $size = 10;

        if ($lastid) {
            $data = $this->db->fetchAll($this->db->select('table.dynamics.*, table.users.screenName author_name')->from('table.dynamics')->join('table.users', 'table.dynamics.authorId = table.users.uid')->where('table.dynamics.did < ? ', $lastid)->order('table.dynamics.did', Typecho_Db::SORT_DESC)->limit($size));
        } else {
            $data = $this->db->fetchAll($this->db->select('table.dynamics.*, table.users.screenName author_name')->from('table.dynamics')->join('table.users', 'table.dynamics.authorId = table.users.uid')->order('table.dynamics.did', Typecho_Db::SORT_DESC)->limit($size));
        }
        $dynamics = array();
        foreach ($data as $dynamic) {
            $dynamic["created"] = date("n\月j\日,Y  H:i:s", $dynamic["created"]);
            $dynamics[] = $dynamic;
        }
        $this->success($dynamics);
    }

    public function deletes()
    {
        if (!$this->user->pass("administrator", true)) {
            $this->error('请登录后台后重试');
        }

        $id = $this->request->get('did', 0);

        if (!$id) {
            $this->success();
        }

        $this->db->query($this->db->delete('table.dynamics')
            ->where('did = ?', $id));

        $this->success();
    }

    public function action()
    {
        $this->db = Typecho_Db::get();
        $this->options = Typecho_Widget::widget('Widget_Options');
        $this->on($this->request->is('do=adds'))->adds();
        $this->on($this->request->is('do=saves'))->saves();
        $this->on($this->request->is('do=lists'))->lists();
        $this->on($this->request->is('do=deletes'))->deletes();

        // $this->response->redirect($this->options->adminUrl);
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Dynamics%2Fmanage-dynamics.php', $this->options->adminUrl));
    }
}