<?php

class Dynamics_Page
{
    private $each_disNums;
    private $nums;
    private $current_page;
    private $sub_pages;
    private $pageNums;
    private $page_array = [];
    private $isPjax;

    /**
     * @param $each_disNums
     * @param $nums
     * @param $current_page
     * @param $sub_pages
     * @param $otherParams
     */
    public function __construct($each_disNums, $nums, $current_page, $sub_pages, $otherParams)
    {
        $this->each_disNums = intval($each_disNums);
        $this->nums = intval($nums);
        if (!$current_page) {
            $this->current_page = 1;
        } else {
            $this->current_page = intval($current_page);
        }
        $this->sub_pages = intval($sub_pages);
        $this->pageNums = ceil($nums / $each_disNums);
        $this->isPjax = $otherParams["isPjax"];
    }

    /**
     * 用来给建立分页的数组初始化的函数。
     * @return array
     */
    public function initArray()
    {
        for ($i = 0; $i < $this->sub_pages; $i++) {
            $this->page_array[$i] = $i;
        }
        return $this->page_array;
    }

    /**
     * construct_num_Page该函数使用来构造显示的条目
     * @return array
     */
    public function construct_num_Page()
    {
        if ($this->pageNums < $this->sub_pages) {
            $current_array = array();
            for ($i = 0; $i < $this->pageNums; $i++) {
                $current_array[$i] = $i + 1;
            }
        } else {
            $current_array = $this->initArray();
            if ($this->current_page <= 3) {
                for ($i = 0; $i < count($current_array); $i++) {
                    $current_array[$i] = $i + 1;
                }
            } elseif ($this->current_page <= $this->pageNums && $this->current_page > $this->pageNums - $this->sub_pages + 1) {
                for ($i = 0; $i < count($current_array); $i++) {
                    $current_array[$i] = ($this->pageNums) - ($this->sub_pages) + 1 + $i;
                }
            } else {
                for ($i = 0; $i < count($current_array); $i++) {
                    $current_array[$i] = $this->current_page - 2 + $i;
                }
            }
        }
        return $current_array;
    }

    /**
     * 构造经典模式的分页
     * @return string
     */
    public function show()
    {
        $str = "";
        if ($this->current_page > 1) {
            $prevPageUrl = $this->buildUrl($this->current_page - 1);
            if ($this->isPjax) {
                $str .= '<li><a href="' . $prevPageUrl . '">&laquo;</a></li>';
            } else {
                $str .= '<li><a href="javascript:void(0)" onclick="window.location.href=\'' . $prevPageUrl . '\'">&laquo;</a></li>';
            }
        } else {
            $str .= '';
        }
        $a = $this->construct_num_Page();

        if (count($a) == 1) {
            return "";
        }

        for ($i = 0; $i < count($a); $i++) {
            $s = $a[$i];
            if ($s == $this->current_page) {
                $url = Typecho_Request::getInstance()->getRequestUrl();
                if ($this->isPjax) {
                    $str .= '<li class="current"><a href="' . $url . '">' . $s . '</a></li>';
                } else {
                    $str .= '<li class="current"><a href="javascript:void(0)" onclick="window.location.href=\'' . $url . '\'">' . $s . '</a></li>';
                }
            } else {
                $url = $this->buildUrl($s);
                if ($this->isPjax) {
                    $str .= '<li><a href="' . $url . '">' . $s . '</a></li>';
                } else {
                    $str .= '<li><a href="javascript:void(0)" onclick="window.location.href=\'' . $url . '\'">' . $s . '</a></li>';
                }
            }
        }
        if ($this->current_page < $this->pageNums) {
            $nextPageUrl = $this->buildUrl($this->current_page + 1);
            if ($this->isPjax) {
                $str .= '<li><a href="' . $nextPageUrl . '">&raquo;</a></li>';
            } else {
                $str .= '<li><a href="javascript:void(0)" onclick="window.location.href=\'' . $nextPageUrl . '\'">&raquo;</a></li>';
            }
        }
        return $str;
    }

    /**
     * @param $page
     * @return string
     */
    private function buildUrl($page)
    {
        return Typecho_Request::getInstance()->makeUriByRequest('dynamicsPage=' . $page);
    }
}
