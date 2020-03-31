<?php
/** @noinspection DuplicatedCode */
include 'common.php';
include 'header.php';
include 'menu.php';
require_once __DIR__ . '/../Dynamics_Page.php';
?>
<?php

$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$request = Typecho_Request::getInstance();
$pagenum = $request->get('page', 1);
$filter = $request->get('filter', 'all');

$query = $db->select('table.dynamics.did',
    'table.dynamics.authorId',
    'table.dynamics.text',
    'table.dynamics.status',
    'table.dynamics.created',
    'table.dynamics.modified',
    'table.users.screenName',
    'table.users.mail')->from('table.dynamics');
$query->join('table.users', 'table.dynamics.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);
$query = $query->order('created', Typecho_Db::SORT_DESC);
$query = $query->page($pagenum, 10);
$rowGoods = $db->fetchAll($query);

$filterOptions = $request->get($filter);
$filterArr = array(
    'filter' => $filter,
    $filter => $filterOptions
);
$count = $db->select('count(1) AS count')->from('table.dynamics');
$page = new Dynamics_Page(10, $db->fetchAll($count)[0]['count'], $pagenum, 10,
    array_merge($filterArr, array(
        'panel' => 'Dynamics/manage/dynamic.php',
        'status' => 'all'
    ))
);

?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">

<!--            <div class="col-mb-12">-->
<!--                <ul class="typecho-option-tabs fix-tabs clearfix">-->
<!--                    <li class="current"><a href="#">动态列表</a></li>-->
<!--                </ul>-->
<!--            </div>-->

            <div class="col-mb-12 typecho-list">

                <div class="col-mb-12 col-tb-12 row" role="main">
                    <form method="post" name="manage_posts" class="operate-form">
                        <div class="typecho-table-wrap">
                            <table class="typecho-list-table">
                                <colgroup>
                                    <col width="5%"/>
                                    <col width="10%"/>
                                    <col width="70%"/>
                                    <col width="15%"/>
                                </colgroup>
                                <thead>
                                <tr>
                                    <th><?php _e('did'); ?></th>
                                    <th><?php _e('创建人'); ?></th>
                                    <th><?php _e('动态内容'); ?></th>
                                    <th><?php _e('创建时间'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($rowGoods as $value) {
                                    ?>
                                    <tr id="<?php echo $value["did"]; ?>">
                                        <td><?php echo $value["did"]; ?></td>
                                        <td><?php echo $value["screenName"]; ?></td>
                                        <td><?php echo $value["text"]; ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', $value["created"]); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="typecho-list-operate clearfix">
                        <ul class="typecho-pager">
                            <?php echo $page->show(); ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
?>
