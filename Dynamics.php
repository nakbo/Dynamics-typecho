<?php
/** @noinspection DuplicatedCode */
include_once 'Dynamics_Abstract.php';
include_once 'Dynamics_Page.php';

class Dynamics extends Dynamics_Abstract
{
    private $_dynamics_list;
    private $_position;
    private $_list_length;

    public $current;
    public $dynamicsNum;
    public $pageNavigator;

    /**
     * Dynamics_ constructor.
     * @param array $params
     */
    public function parse($params = array())
    {
        $page = $this->request->get('dynamicsPage', 1);
        $this->current = $page;
        $pageSize = intval($params["pageSize"]);
        $pageSize = $pageSize > 0 ? $pageSize : 5;

        $select = $this->db->select(
            'table.dynamics.*',
            'table.users.screenName',
            'table.users.mail')
            ->where("table.dynamics.status != ?", "hidden")
            ->from('table.dynamics');
        $select->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);

        $select = $select->order('table.dynamics.created', Typecho_Db::SORT_DESC);
        $select = $select->page($page, $pageSize);

        $this->_dynamics_list = $this->db->fetchAll($select);
        $this->dynamicsNum = $this->db->fetchAll(
            $this->db->select('count(1) AS count')->from('table.dynamics')
        )[0]['count'];
        $this->pageNavigator = new Dynamics_Page(
            $pageSize, $this->dynamicsNum, $page, 4, array(
                "isPjax" => boolval($params["isPjax"])
            )
        );

        $this->_position = 0;
        $this->_list_length = count($this->_dynamics_list);

    }

    /**
     * 遍历
     * @return array|bool
     */
    public function next()
    {
        if ($this->_list_length > $this->_position) {
            $dic = $this->_dynamics_list[$this->_position];
            $this->setDid($dic['did']);
            $this->setStatus($dic['status']);
            $this->setAuthorId($dic['authorId']);
            $this->setMail($dic['mail']);
            $this->setAuthorName($dic['screenName']);
            $this->setText($dic['text']);
            $this->setCreated($dic['created']);
            $this->setModified($dic['modified']);
            $this->setAvatar($dic['avatar']);
            $this->setAgent($dic['agent']);
            $this->_position++;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 当前页面位置
     * @return void
     */
    public function current()
    {
        echo $this->current;
    }

    /**
     * 分页布局
     */
    public function navigator()
    {
        echo "<ol class=\"dynamics-page-navigator\">" . $this->pageNavigator->show() . "</ol>";
    }

}
