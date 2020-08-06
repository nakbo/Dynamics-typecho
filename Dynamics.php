<?php
/** @noinspection DuplicatedCode */
include_once 'Dynamics_Abstract.php';
include_once 'Dynamics_Page.php';

class Dynamics extends Dynamics_Abstract
{
    private $_dynamics_list = array();
    private $_have;
    private $_position;
    private $pageNavigator;

    public $current;

    /**
     * Dynamics_ constructor.
     * @param array $params
     */
    public function parse($params = array())
    {
        $page = $this->request->get('dynamicsPage', 1);
        $this->current = $page;
        $pageSize = $params["pageSize"] ?: 5;

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
        $count = $this->db->select('count(1) AS count')->from('table.dynamics');
        $count = $this->db->fetchAll($count)[0]['count'];
        $this->pageNavigator = new Dynamics_Page($pageSize, $count, $page, 4,
            array(), false, $params["isPjax"] ?: false
        );
        $this->_have = count($this->_dynamics_list) > 0;
        $this->_position = 0;

    }

    /**
     * 遍历
     * @return array|bool
     */
    public function next()
    {
        if ($this->_have) {
            $dic = $this->_dynamics_list[$this->_position];
            $this->setDid($dic['did']);
            $this->setStatus($dic['status']);
            $this->setAuthorId($dic['authorId']);
            $this->setMail($dic['mail']);
            $this->setAuthorName($dic['screenName']);
            $this->setText($dic['text']);
            $this->setCreated($dic['created']);
            $this->setModified($dic['modified']);
            $this->_position++;
            $this->_have = count($this->_dynamics_list) > $this->_position;
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