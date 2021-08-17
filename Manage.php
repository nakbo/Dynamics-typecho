<?php
include 'header.php';
include 'menu.php';
$post = new Widget_Contents_Post_Edit(
    Typecho_Request::getInstance(), Typecho_Response::getInstance()
);
$post->push([
    'isMarkdown' => true
]);
?>
<style type="text/css">
    .dynamic-row {
        background-color: #fff;
        word-wrap: break-word;
        word-break: break-all;
    }

    .dynamic-row.row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .dynamic-left {
        padding-left: 0 !important;
        padding-right: 0 !important;
        /*border-right: 6px #e4dad1 solid;*/
        border-right: 8px rgba(140, 162, 157, 0.15) solid;
        min-height: 400px !important;
    }

    .dynamic-add {
        border-bottom: 1px #e4dad1 solid;
        height: 40px;
        line-height: 40px;
        text-align: center;
        display: block;
    }

    .dynamic-list-item {
        /*border-top: 1px #e4dad1 solid; */
        border-bottom: 1px #e4dad1 solid;
        padding: 10px 10px 10px 10px;
        font-size: 14px;
        background-color: #fff;
        cursor: pointer;
    }

    .dynamic-list-item:hover {
        background-color: #F6F6F3;
    }

    .dynamic-list-item.active {
        background-color: rgba(94, 169, 169, 0.17);
    }

    .dynamic-list-item div.title {

    }

    .dynamic-list-item div.subtitle {
        font-size: 10px;
        margin-top: 3px;
        color: #827C7C;
    }

    .dynamic-list-item div.subtitle .author {
        float: right;
    }

    .dynamic-left .dynamic-more-load {
        cursor: pointer;
    }

    .dynamic-left .dynamic-more-load .loading {
        display: none;
    }

    .dynamic-left .dynamic-more-no {
        display: none;
    }

    .dynamic-left .dynamic-more-load, .dynamic-left .dynamic-more-no {
        text-align: center;
        padding: 10px 10px;
    }

    .dynamic-right {
        padding: 10px 10px 10px 10px;
        /*border-left: 8px #e4dad1 solid;*/
        border-left: 8px rgba(140, 162, 157, 0.15) solid;
        margin-left: -8px;
        min-height: 600px !important;
    }

    .dynamic-right .title {
        display: none;
    }

    .dynamic-right .save {
        display: none;
    }

    #dynamic-btn-box {
        float: right;
        margin-top: 20px;
    }

</style>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">

            <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li class="current"><a href="#">我的动态</a></li>
                    <li><a href="<?php Helper::options()->index(Dynamics_Plugin::DYNAMICS_ROUTE); ?>" target="_blank">动态首页</a>
                    </li>
                    <li>
                        <a href="<?php Helper::options()->adminUrl('extending.php?panel=Dynamics%2FThemes.php'); ?>">主题设置</a>
                    </li>
                    <li>
                        <a href="<?php Helper::options()->adminUrl('options-plugin.php?config=Dynamics'); ?>">插件设置</a>
                    </li>
                </ul>
            </div>

            <div class="col-mb-12 typecho-list">
                <div class="dynamic-row row">
                    <div class="col-mb-4 dynamic-left">
                        <div class="dynamic-list">
                            <div class="dynamic-add">我的动态</div>
                            <div class="dynamic-body"></div>
                            <div class="dynamic-more-load" href="javascript:void(0)">
                                <span>加载更多</span>
                                <span class="loading">中...</span>
                            </div>
                            <div class="dynamic-more-no">没有更多动态了</div>
                        </div>
                    </div>
                    <div class="col-mb-8 dynamic-right">
                        <p>
                            <label for="text" class="sr-only"><?php _e('文章内容'); ?></label>
                            <textarea placeholder="请输入正文" style="height: <?php $options->editorSize(); ?>px"
                                      autocomplete="off" id="text" name="text" class="w-100 mono"></textarea>
                        </p>
                    </div>
                </div>

                <div id="dynamic-btn-box">
                    <button type="submit" name="do" value="adds" class="adds btn">新建动态</button>
                    <button type="submit" name="do" value="delete" class="delete btn">删除此动态</button>
                    <button type="submit" name="do" value="publish" class="save btn primary" id="btn-submit">保存动态
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'editor-js.php';
?>
<script>
    $(document).ready(function () {
        function layoutOf(data) {
            return '<div class="dynamic-list-item" data-id="' + data.did + '">' +
                '<div class="title">' + data.title + '<a href="' + data.url + '" target="_blank"><i class="i-exlink"></i></a></div>' +
                '<div class="subtitle">' +
                '<span class="desc">' + data.desc + '</span>' +
                '<span class="author">' + data.nickname + '</span>' +
                '</div>' +
                '</div>';
        }

        function message(notice, noticeType = 'error') {
            let head = $('.typecho-head-nav'),
                p = $('<div class="message popup ' + noticeType + '">'
                    + '<ul><li>' + notice + '</li></ul></div>'), offset = 0;

            if (head.length > 0) {
                p.insertAfter(head);
                offset = head.outerHeight();
            } else {
                p.prependTo(document.body);
            }

            function checkScroll() {
                if ($(window).scrollTop() >= offset) {
                    p.css({
                        'position': 'fixed',
                        'top': 0
                    });
                } else {
                    p.css({
                        'position': 'absolute',
                        'top': offset
                    });
                }
            }

            $(window).scroll(function () {
                checkScroll();
            });

            checkScroll();

            p.slideDown(function () {
                let t = $(this), color = '#C6D880';
                if (t.hasClass('error')) {
                    color = '#FBC2C4';
                } else if (t.hasClass('notice')) {
                    color = '#FFD324';
                }

                t.effect('highlight', {color: color})
                    .delay(5000).fadeOut(function () {
                    $(this).remove();
                });
            });
        }

        let lastDid = 0, selectId = 0, dynData = [],
            actionUrl = '<?php Helper::options()->index("action/dynamics?do=");?>';

        function loadDynamics() {
            $.get(actionUrl + 'list', {
                lastDid: lastDid
            }, function (data) {
                if (data.code) {
                    let len = data.data.length;
                    if (len === 0) {
                        $('.dynamic-left .dynamic-more-load').hide();
                        $('.dynamic-left .dynamic-more-no').show();
                        message('没有动态噢', 'notice');
                    } else {
                        lastDid = data.data[len - 1].did;
                        let temp = '';
                        for (let i = 0; i < len; i++) {
                            dynData['key_' + data.data[i].did] = data.data[i];
                            temp += layoutOf(data.data[i]);
                        }

                        $('.dynamic-list .dynamic-body').append(temp);
                        $('.dynamic-more-load .loading').hide();

                        if (!selectId) {
                            $('.dynamic-list .dynamic-list-item[data-id=' + data.data[0].did + ']').click();
                            $('.wmd-edittab a[href="#wmd-preview"]').click();
                        }
                    }
                } else {
                    message(data.msg);
                }
            });
        }

        let textarea = $('#text');
        $(document).on('click', '.dynamic-list .dynamic-list-item', function () {
            $('.dynamic-list .dynamic-list-item').each(function (k, ele) {
                $(ele).removeClass('active');
            });
            $(this).addClass('active');
            selectId = $(this).attr("data-id");

            let dyn = dynData['key_' + $(this).attr("data-id")];
            textarea.val(dyn.text);
            textarea[0].dispatchEvent(new Event('input'));
        });

        $(document).on('click', '#dynamic-btn-box .adds', function () {
            $.get(actionUrl + 'add', {}, function (data) {
                if (data.code) {
                    message('新增成功', 'success');
                    dynData['key_' + data.data.did] = data.data;
                    let temp = layoutOf(data.data);
                    temp = $(temp);
                    $('.dynamic-list .dynamic-body').prepend(temp);

                    temp.click();
                    $('.wmd-edittab a[href="#wmd-editarea"]').click();
                    $('.dynamic-right .title').focus();
                } else {
                    message(data.msg);
                }
            })
        });

        $(document).on('click', '.dynamic-list .dynamic-more-load', function () {
            $('.dynamic-more-load .loading').show();
            loadDynamics();
        });

        $(document).on('click', '#dynamic-btn-box .save', function () {
            if (selectId) {
                $.get(actionUrl + 'save', {
                    did: selectId,
                    title: $('.dynamic-right .title').val(),
                    text: $('#text').val(),
                }, function (data) {
                    if (data.code) {
                        message('保存成功', 'success');
                        dynData['key_' + data.data.did] = data.data;
                        let list_item = $('.dynamic-body .dynamic-list-item[data-id="' + data.data.did + '"]');
                        list_item.find('.subtitle .desc').html(data.data.desc);
                    } else {
                        message(data.msg);
                    }
                })
            }
        });

        $(document).on('click', '#dynamic-btn-box .delete', function () {
            if (confirm('确定删除该动态')) {
                if (selectId) {
                    $.get(actionUrl + 'remove', {
                        did: selectId
                    }, function (data) {
                        if (data.code) {
                            message('删除成功', 'success');
                            delete dynData['key_' + selectId];
                            let list_item = $('.dynamic-body .dynamic-list-item[data-id="' + selectId + '"]');
                            if (list_item.prev().length !== 0) {
                                list_item.prev().click();
                            } else if (list_item.next().length !== 0) {
                                list_item.next().click();
                            } else {
                                $('.dynamic-right').hide();
                            }
                            list_item.remove();
                        } else {
                            message(data.msg);
                        }
                    });
                }
            }
        });

        loadDynamics();
    });
</script>

<?php include 'footer.php'; ?>
