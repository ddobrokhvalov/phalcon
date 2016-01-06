<h2>Поиск admins</h2>
<form action="/admin/admins/search" method="post">
    <fieldset>

<fieldset>

    <?php foreach ($form as $element) { ?>
    <div class="control-group">
        <?php echo $element->label(array('class' => 'control-label')); ?>
        <div class="controls"><?php echo $element; ?></div>
    </div>
    <?php } ?>

    <div class="control-group">
        <?php echo $this->tag->submitButton(array('Search', 'class' => 'btn btn-primary')); ?>
    </div>

</fieldset>
</form>




<div><a href="/admin/admins/add">Add admin</a></div>