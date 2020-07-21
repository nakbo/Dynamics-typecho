<?php
include_once 'Dynamics_Abstract.php';
include_once 'Dynamics_Page.php';

class Dynamics extends Dynamics_Abstract
{
    private $_dynamics_list = array();
    private $_have;
    private $_position;
    private $pageNavigator;

    /**
     * Dynamics_ constructor.
     * @param array $params
     */
    public function parse($params = array())
    {
        $page = $this->request->get('dynamicsPage', 1);
        $pageSize = $params["pageSize"] ?: 5;

        $select = $this->db->select('table.dynamics.did',
            'table.dynamics.authorId',
            'table.dynamics.text',
            'table.dynamics.status',
            'table.dynamics.created',
            'table.dynamics.modified',
            'table.users.screenName',
            'table.users.mail')->from('table.dynamics');
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

    public function next()
    {
        if ($this->_have) {
            $dic = $this->_dynamics_list[$this->_position];
            $this->setDid($dic['did']);
            $this->setAuthorId($dic['authorId']);
            $this->setMail($dic['mail']);
            $this->setAuthorName($dic['screenName']);
            $this->setStatus($dic['status']);
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
     * @return int
     */
    public function current()
    {
        return $this->_position + 1;
    }

    public function navigator()
    {
        echo "<ol class=\"dynamics-page-navigator\">" . $this->pageNavigator->show() . "</ol>";
    }

}