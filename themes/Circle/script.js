window.function = {};
window.function.register = function (func, name, execute = true) {
    window.function[name] = func;
    if (execute) {
        func();
    }
}
window.function.execute = function () {
    for (let i = 0; i < arguments.length; i++) {
        window.function[arguments[i]]();
    }
}

window.function.image = function () {
    let able = false;
    $.each(['.message img'], function (i, key) {
        $(key).each(function () {
            if (able && $(this).attr("title") !== "" && $(this).attr("title") !== "请输入图片描述") {
                let setWith = this.width > 9 ? this.width + "px" : "100%";
                $(this).after("<p style='text-align:center;margin:5px 0 0 0;width:" + setWith + "'>" + $(this).attr("title") + "</p>")
            }
            $(this).wrap("<a class='gallery' data-fancybox='gallery' no-pjax data-type='image' data-caption='" +
                $(this).attr("title") + "' href='" +
                $(this).attr(able ? "src" : "data-src") + "'></a>");
        });
        able = true;
    });
    $('.message p').each(function () {
        let images = $(this).find('a.gallery');
        if (images.length >= 2) {
            $(this).addClass('galleries');
            $(this).addClass('gallery-' + (images.length > 4 ? 3 : images.length));
        }
    });

    // lazy
    lazyload();
}