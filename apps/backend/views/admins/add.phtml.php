<form action="/admin/admins/create" method="post">
    <fieldset>

        <fieldset>

            <?php foreach ($form as $element) { ?>
            <div class="control-group">
                <?php echo $element->label(array('class' => 'control-label')); ?>
                <div class="controls"><?php echo $element; ?></div>
            </div>
            <?php } ?>

            <div class="control-group">
                <?php echo $this->tag->submitButton(array('Создать', 'class' => 'btn btn-primary')); ?>
            </div>

        </fieldset>
</form>