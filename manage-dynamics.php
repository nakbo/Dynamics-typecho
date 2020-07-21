<?php
include 'header.php';
include 'menu.php';
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

    .dynamic-left .dynamic-loadmore {
        cursor: pointer;
    }

    .dynamic-left .dynamic-loadmore .loading {
        display: none;
    }

    .dynamic-left .dynamic-nomore {
        display: none;
    }

    .dynamic-left .dynamic-loadmore, .dynamic-left .dynamic-nomore {
        text-align: center;
        padding: 10px 10px;
    }

    .dynamic-right {
        padding: 10px 10px 10px 10px;
        /*border-left: 8px #e4dad1 solid;*/
        border-left: 8px rgba(140, 162, 157, 0.15) solid;
        margin-left: -8px;
        min-height: 600px !important;
        display: none;
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
                    <li><a href="<?php Dynamics_Plugin::homeUrl(); ?>" target="_blank">动态首页</a></li>
                    <li>
                        <a href="<?php echo Helper::options()->adminUrl . 'options-plugin.php?config=Dynamics'; ?>">设置</a>
                    </li>
                </ul>
            </div>

            <div class="col-mb-12 typecho-list">
                <div class="dynamic-row row">

                    <div class="col-mb-4 dynamic-left">
                        <div class="dynamic-list">
                            <div class="dynamic-add">我的动态</div>
                            <div class="dynamic-body"></div>
                            <div class="dynamic-loadmore" href="javascript:void(0)">
                                <span>加载更多</span>
                                <?php
                                $loading_ball = Typecho_Common::url('/usr/plugins/Dynamics/balls.gif', $options->siteUrl);
                                echo '<span class="loading"><img class="avatar" src="' . $loading_ball . '" alt="loading" width="12" height="12"/></span>';
                                ?>
                            </div>
                            <div class="dynamic-nomore">没有更多动态了</div>
                        </div>
                    </div>
                    <div></div>
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
include 'footer.php';
?>

<script src="<?php $options->adminStaticUrl('js', 'pagedown.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'pagedown-extra.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'diff.js?v=' . $suffixVersion); ?>"></script>

<script>
    $(document).ready(function () {
        var textarea = $('#text'),
            toolbar = $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent()),
            preview = $('<div id="wmd-preview" class="wmd-hidetab" />').insertAfter('.editor');
        var options = {};

        options.strings = {
            bold: '<?php _e('加粗'); ?> <strong> Ctrl+B',
            boldexample: '<?php _e('加粗文字'); ?>',

            italic: '<?php _e('斜体'); ?> <em> Ctrl+I',
            italicexample: '<?php _e('斜体文字'); ?>',

            link: '<?php _e('链接'); ?> <a> Ctrl+L',
            linkdescription: '<?php _e('请输入链接描述'); ?>',

            quote: '<?php _e('引用'); ?> <blockquote> Ctrl+Q',
            quoteexample: '<?php _e('引用文字'); ?>',

            code: '<?php _e('代码'); ?> <pre><code> Ctrl+K',
            codeexample: '<?php _e('请输入代码'); ?>',

            image: '<?php _e('图片'); ?> <img> Ctrl+G',
            imagedescription: '<?php _e('请输入图片描述'); ?>',

            olist: '<?php _e('数字列表'); ?> <ol> Ctrl+O',
            ulist: '<?php _e('普通列表'); ?> <ul> Ctrl+U',
            litem: '<?php _e('列表项目'); ?>',

            heading: '<?php _e('标题'); ?> <h1>/<h2> Ctrl+H',
            headingexample: '<?php _e('标题文字'); ?>',

            hr: '<?php _e('分割线'); ?> <hr> Ctrl+R',
            more: '<?php _e('摘要分割线'); ?> <!--more--> Ctrl+M',

            undo: '<?php _e('撤销'); ?> - Ctrl+Z',
            redo: '<?php _e('重做'); ?> - Ctrl+Y',
            redomac: '<?php _e('重做'); ?> - Ctrl+Shift+Z',

            fullscreen: '<?php _e('全屏'); ?> - Ctrl+J',
            exitFullscreen: '<?php _e('退出全屏'); ?> - Ctrl+E',
            fullscreenUnsupport: '<?php _e('此浏览器不支持全屏操作'); ?>',

            imagedialog: '<p><b><?php _e('插入图片'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的远程图片地址'); ?></p><p><?php _e('您也可以使用附件功能插入上传的本地图片'); ?></p>',
            linkdialog: '<p><b><?php _e('插入链接'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的链接地址'); ?></p>',

            ok: '<?php _e('确定'); ?>',
            cancel: '<?php _e('取消'); ?>',

            help: '<?php _e('Markdown语法帮助'); ?>'
        };

        var converter = new Markdown.Converter(),
            editor = new Markdown.Editor(converter, '', options),
            diffMatch = new diff_match_patch(), last = '', preview = $('#wmd-preview'),
            mark = '@mark' + Math.ceil(Math.random() * 100000000) + '@',
            span = '<span class="diff" />',
            cache = {};

        Markdown.Extra.init(converter, {
            'extensions': ["tables", "fenced_code_gfm", "def_list", "attr_list", "footnotes", "strikethrough", "newlines"]
        });
        // 自动跟随
        converter.hooks.chain('postConversion', function (html) {
            // clear special html tags
            html = html.replace(/<\/?(\!doctype|html|head|body|link|title|input|select|button|textarea|style|noscript)[^>]*>/ig, function (all) {
                return all.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/'/g, '&#039;')
                    .replace(/"/g, '&quot;');
            });

            // clear hard breaks
            html = html.replace(/\s*((?:<br>\n)+)\s*(<\/?(?:p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|li|dd|dt)[^\w])/gm, '$2');

            if (html.indexOf('<!--more-->') > 0) {
                var parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                    summary = parts.shift(),
                    details = parts.join('');

                html = '<div class="summary">' + summary + '</div>'
                    + '<div class="details">' + details + '</div>';
            }


            var diffs = diffMatch.diff_main(last, html);
            last = html;

            if (diffs.length > 0) {
                var stack = [], markStr = mark;

                for (var i = 0; i < diffs.length; i++) {
                    var diff = diffs[i], op = diff[0], str = diff[1]
                    sp = str.lastIndexOf('<'), ep = str.lastIndexOf('>');

                    if (op != 0) {
                        if (sp >= 0 && sp > ep) {
                            if (op > 0) {
                                stack.push(str.substring(0, sp) + markStr + str.substring(sp));
                            } else {
                                var lastStr = stack[stack.length - 1], lastSp = lastStr.lastIndexOf('<');
                                stack[stack.length - 1] = lastStr.substring(0, lastSp) + markStr + lastStr.substring(lastSp);
                            }
                        } else {
                            if (op > 0) {
                                stack.push(str + markStr);
                            } else {
                                stack.push(markStr);
                            }
                        }

                        markStr = '';
                    } else {
                        stack.push(str);
                    }
                }

                html = stack.join('');

                if (!markStr) {
                    var pos = html.indexOf(mark), prev = html.substring(0, pos),
                        next = html.substr(pos + mark.length),
                        sp = prev.lastIndexOf('<'), ep = prev.lastIndexOf('>');

                    if (sp >= 0 && sp > ep) {
                        html = prev.substring(0, sp) + span + prev.substring(sp) + next;
                    } else {
                        html = prev + span + next;
                    }
                }
            }

            // 替换img
            html = html.replace(/<(img)\s+([^>]*)\s*src="([^"]+)"([^>]*)>/ig, function (all, tag, prefix, src, suffix) {
                if (!cache[src]) {
                    cache[src] = false;
                } else {
                    return '<span class="cache" data-width="' + cache[src][0] + '" data-height="' + cache[src][1] + '" '
                        + 'style="background:url(' + src + ') no-repeat left top; width:'
                        + cache[src][0] + 'px; height:' + cache[src][1] + 'px; display: inline-block; max-width: 100%;'
                        + '-webkit-background-size: contain;-moz-background-size: contain;-o-background-size: contain;background-size: contain;" />';
                }

                return all;
            });

            // 替换block
            html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
                if (src[src.length - 1] == '/') {
                    src = src.substring(0, src.length - 1);
                }

                return '<div style="background: #ddd; height: 40px; overflow: hidden; line-height: 40px; text-align: center; font-size: 12px; color: #777">'
                    + tag + ' : ' + $.trim(src) + '</div>';
            });

            return html;
        });

        function cacheResize() {
            var t = $(this), w = parseInt(t.data('width')), h = parseInt(t.data('height')),
                ow = t.width();

            t.height(h * ow / w);
        }

        var to;
        editor.hooks.chain('onPreviewRefresh', function () {
            var diff = $('.diff', preview), scrolled = false;

            if (to) {
                clearTimeout(to);
            }

            $('img', preview).load(function () {
                var t = $(this), src = t.attr('src');

                if (scrolled) {
                    preview.scrollTo(diff, {
                        offset: -50
                    });
                }

                if (!!src && !cache[src]) {
                    cache[src] = [this.width, this.height];
                }
            });

            $('.cache', preview).resize(cacheResize).each(cacheResize);

            var changed = $('.diff', preview).parent();
            if (!changed.is(preview)) {
                changed.css('background-color', 'rgba(255,230,0,0.5)');
                to = setTimeout(function () {
                    changed.css('background-color', 'transparent');
                }, 4500);
            }

            if (diff.length > 0) {
                var p = diff.position(), lh = diff.parent().css('line-height');
                lh = !!lh ? parseInt(lh) : 0;

                if (p.top < 0 || p.top > preview.height() - lh) {
                    preview.scrollTo(diff, {
                        offset: -50
                    });
                    scrolled = true;
                }
            }
        });

        <?php //Typecho_Plugin::factory('admin/editor-js.php')->markdownEditor($content); ?>

        var input = $('#text'), th = textarea.height(), ph = preview.height(),
            uploadBtn = $('<button type="button" id="btn-fullscreen-upload" class="btn btn-link">'
                + '<i class="i-upload"><?php _e('附件'); ?></i></button>')
                .prependTo('.submit .right')
                .click(function () {
                    $('a', $('.typecho-option-tabs li').not('.active')).trigger('click');
                    return false;
                });

        $('.typecho-option-tabs li').click(function () {
            uploadBtn.find('i').toggleClass('i-upload-active',
                $('#tab-files-btn', this).length > 0);
        });

        editor.hooks.chain('enterFakeFullScreen', function () {
            th = textarea.height();
            ph = preview.height();
            $(document.body).addClass('fullscreen');
            var h = $(window).height() - toolbar.outerHeight();

            textarea.css('height', h);
            preview.css('height', h);
        });

        editor.hooks.chain('enterFullScreen', function () {
            $(document.body).addClass('fullscreen');

            var h = window.screen.height - toolbar.outerHeight();
            textarea.css('height', h);
            preview.css('height', h);
        });

        editor.hooks.chain('exitFullScreen', function () {
            $(document.body).removeClass('fullscreen');
            textarea.height(th);
            preview.height(ph);
        });

        function initMarkdown() {
            editor.run();

            var imageButton = $('#wmd-image-button'),
                linkButton = $('#wmd-link-button');

            Typecho.insertFileToEditor = function (file, url, isImage) {
                var button = isImage ? imageButton : linkButton;

                options.strings[isImage ? 'imagename' : 'linkname'] = file;
                button.trigger('click');

                var checkDialog = setInterval(function () {
                    if ($('.wmd-prompt-dialog').length > 0) {
                        $('.wmd-prompt-dialog input').val(url).select();
                        clearInterval(checkDialog);
                        checkDialog = null;
                    }
                }, 10);
            };

            Typecho.uploadComplete = function (file) {
                Typecho.insertFileToEditor(file.title, file.url, file.isImage);
            };

            // 编辑预览切换
            var edittab = $('.editor').prepend('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('编辑'); ?></a><a href="#wmd-preview"><?php _e('详情'); ?></a></div>'),
                editarea = $(textarea.parent()).attr("id", "wmd-editarea");

            $(".wmd-edittab a").click(function () {
                $(".wmd-edittab a").removeClass('active');
                $(this).addClass("active");
                $("#wmd-editarea, #wmd-preview").addClass("wmd-hidetab");

                var selected_tab = $(this).attr("href"),
                    selected_el = $(selected_tab).removeClass("wmd-hidetab");

                // 预览时隐藏编辑器按钮
                if (selected_tab === "#wmd-preview") {
                    $('#dynamic-btn-box .save').hide();
                    $("#dynamic-btn-box .delete").hide();
                    $("#wmd-button-row").addClass("wmd-visualhide");
                } else {
                    $('#dynamic-btn-box .save').show();
                    $('#dynamic-btn-box .delete').show();
                    $("#wmd-button-row").removeClass("wmd-visualhide");
                }

                // 预览和编辑窗口高度一致
                $("#wmd-preview").outerHeight($("#wmd-editarea").innerHeight());

                return false;
            });

            // $('.wmd-edittab a[href="#wmd-preview"]').click();
        }

        initMarkdown();

        $(document).on('click', '.dynamic-list .dynamic-list-item', function () {
            $('.dynamic-list .dynamic-list-item').each(function (k, ele) {
                $(ele).removeClass('active');
            });
            $(this).addClass('active');

            var note = notedata['key_' + $(this).attr("data-id")];

            selectId = $(this).attr("data-id");

            $('#text').val(note.text);

            editor.refreshPreview();

            $('.dynamic-right').show();
            // $('.dynamic-right').html($(this).attr("data-id"));
        });

        $(document).on('click', '#dynamic-btn-box .adds', function () {
            $.get('/action/dynamics-manage?do=adds', {}, function (data) {
                if (data.result) {
                    notedata['key_' + data.data.did] = data.data;

                    var note_item_tpl =
                        '<div class="dynamic-list-item" data-id="' + data.data.did + '">' +
                        '<div class="title">' + data.data.title + '<a href="' + data.data.url + '" target="_blank"><i class="i-exlink"></i></a></div>' +
                        '<div class="subtitle">' +
                        '<span class="desc">' + data.data.desc + '</span>' +
                        '<span class="author">' + data.data.author_name + '</span>' +
                        '</div>' +
                        '</div>';
                    note_item_tpl = $(note_item_tpl);
                    $('.dynamic-list .dynamic-body').prepend(note_item_tpl);
                    note_item_tpl.click();
                    $('.wmd-edittab a[href="#wmd-editarea"]').click();
                    $('.dynamic-right .title').focus();
                } else {
                    alert(data.message);
                }
            })
        });

        $(document).on('click', '.dynamic-list .dynamic-loadmore', function () {
            $('.dynamic-left .dynamic-loadmore .loading').show();
            loadDynamics();
        });

        $(document).on('click', '#dynamic-btn-box .save', function () {
            if (selectId) {
                $.get('/action/dynamics-manage?do=saves', {
                    did: selectId,
                    title: $('.dynamic-right .title').val(),
                    text: $('#text').val(),
                }, function (data) {
                    if (data.result) {
                        notedata['key_' + data.data.did] = data.data;
                        var list_item = $('.dynamic-body .dynamic-list-item[data-id="' + data.data.did + '"]');
                        list_item.find('.subtitle .desc').html(data.data.desc);
                    } else {
                        alert(data.message);
                    }
                })
            }
        });

        $(document).on('click', '#dynamic-btn-box .delete', function () {
            if (confirm('确定删除该动态')) {
                if (selectId) {
                    $.get('/action/dynamics-manage?do=deletes', {
                        did: selectId
                    }, function (data) {
                        if (data.result) {
                            delete notedata['key_' + selectId];
                            var list_item = $('.dynamic-body .dynamic-list-item[data-id="' + selectId + '"]');
                            if (list_item.prev().length !== 0) {
                                list_item.prev().click();
                            } else if (list_item.next().length !== 0) {
                                list_item.next().click();
                            } else {
                                $('.dynamic-right').hide();
                            }
                            list_item.remove();
                        } else {
                            alert(data.message);
                        }
                    })
                }
            }
        });

        var lastdid = 0;
        var selectId = 0;
        var notedata = [];

        function loadDynamics() {
            $.get('/action/dynamics-manage?do=lists', {
                lastdid: lastdid
            }, function (data) {
                if (data.result) {
                    var len = data.data.length;
                    if (len === 0) {
                        // return;
                        $('.dynamic-left .dynamic-loadmore').hide();
                        $('.dynamic-left .dynamic-nomore').show();
                    } else {
                        lastdid = data.data[len - 1].did;
                        var note_item_tpl = '';
                        for (var i = 0; i < len; i++) {
                            notedata['key_' + data.data[i].did] = data.data[i];
                            note_item_tpl +=
                                '<div class="dynamic-list-item" data-id="' + data.data[i].did + '">' +
                                '<div class="title">' + data.data[i].title + '<a href="' + data.data[i].url + '" target="_blank"><i class="i-exlink"></i></a></div>' +
                                '<div class="subtitle">' +
                                '<span class="desc">' + data.data[i].desc + '</span>' +
                                '<span class="author">' + data.data[i].author_name + '</span>' +
                                '</div>' +
                                '</div>'
                        }
                        // console.log(note_item_tpl);
                        $('.dynamic-list .dynamic-body').append(note_item_tpl);
                        $('.dynamic-left .dynamic-loadmore .loading').hide();

                        if (!selectId) {
                            $('.dynamic-list .dynamic-list-item[data-id=' + data.data[0].did + ']').click();
                            $('.wmd-edittab a[href="#wmd-preview"]').click();
                        }
                    }
                } else {
                    alert(data.message);
                }
            })
        }

        loadDynamics();
    });
</script>