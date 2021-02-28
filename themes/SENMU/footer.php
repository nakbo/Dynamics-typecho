<?php
/**
 * 公用尾部
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
?>

</div><!-- end .container -->
<footer>
    &copy; <?php echo date('Y') . ' <a href="' . $this->options->siteUrl . '">' . $this->options->title . '</a>'; ?>
    <br>
    Powered by <a href="http://typecho.org" target="_blank">Typecho</a> && <a href="https://github.com/kraity/Dynamics"
                                                                              target="_blank">Nabo Dynamics</a>
</footer>
<?php $this->footer(); ?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<script>
    console.log('Nabo Dynamics\n\n@dev krait\n@theme ShangJixin');

    $(document).pjax('a[href^="<?php echo $this->options->siteUrl; ?>"]:not(a[target="_blank"], a[no-pjax])', {
        container: '#pjax-container',
        fragment: '#pjax-container',
        timeout: 16000
    }).on('pjax:complete', function () {

    }).on('pjax:send', function () {
        $('body,html').animate({scrollTop: 0}, 500);
    }).on('pjax:end', function () {

    });
</script>
</body>
</html>
