# Dynamics

**我的动态**

## 使用教程

 - https://nabo.krait.cn/docs/#/course-dynamics
 - https://github.com/krait-team/Dynamics-typecho/wiki

**使用动态主题的page.php**

```php
<?php Dynamics_Plugin::output(); ?>
```

**使用自定义在任意地方调用**

```php
<?php $dyn = Dynamics_Plugin::get(); ?>

<?php while ($dyn->dynamics->next()) : ?>
    <ul>
        <li>=================================================</li>
        <li>did: <?php $dyn->dynamic->did() ?></li>
        <li>mail: <?php $dyn->dynamic->mail() ?></li>
        <li>avatar: <?php $dyn->dynamic->avatar() ?></li>
        <li>authorName: <?php $dyn->dynamic->authorName() ?></li>
        <li>url: <?php $dyn->dynamic->url() ?></li>
        <li>created: <?php $dyn->dynamic->created() ?></li>
        <li>deviceTag: <?php $dyn->dynamic->deviceTag() ?></li>
        <li>content: <?php $dyn->dynamic->content() ?></li>
        <li>authorId: <?php $dyn->dynamic->authorId() ?></li>
        <li>agent: <?php $dyn->dynamic->agent() ?></li>
        <li>status: <?php $dyn->dynamic->status() ?></li>
        <li>----------------------------------------------</li>
        <li>did: <?php echo $dyn->dynamic->did ?></li>
        <li>mail: <?php echo $dyn->dynamic->mail ?></li>
        <li>avatar: <?php echo $dyn->dynamic->avatar ?></li>
        <li>authorName: <?php echo $dyn->dynamic->authorName ?></li>
        <li>url: <?php echo $dyn->dynamic->url ?></li>
        <li>created: <?php echo $dyn->dynamic->created ?></li>
        <li>content: <?php echo $dyn->dynamic->content ?></li>
        <li>authorId: <?php echo $dyn->dynamic->authorId ?></li>
        <li>agent: <?php echo $dyn->dynamic->agent ?></li>
        <li>status: <?php echo $dyn->dynamic->status ?></li>

        <li>====================================================</li>
    </ul>

<?php endwhile; ?>

<?php $dyn->dynamics->navigator() ?>
```
