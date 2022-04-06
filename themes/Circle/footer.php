<div class="sidebar">
    <div class="columns">
        <div class="column copyright">
            <p><?php $this->option->copyright(); ?></p>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
<script type="text/javascript">window.function.execute('image');</script>
<?php $this->footer(); ?>
<script>
    $(document).pjax('a[href^="<?php echo $this->options->siteUrl; ?>"]:not(a[target="_blank"], a[no-pjax])', {
        container: '.article',
        fragment: '.article',
        timeout: 8000
    }).on('pjax:complete', function () {
        window.function.execute('image');
    });
</script>
</body>
</html>