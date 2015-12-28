<?php $v4619004751iterated = false; ?><?php $v4619004751iterator = $page->items; $v4619004751incr = 0; $v4619004751loop = new stdClass(); $v4619004751loop->length = count($v4619004751iterator); $v4619004751loop->index = 1; $v4619004751loop->index0 = 1; $v4619004751loop->revindex = $v4619004751loop->length; $v4619004751loop->revindex0 = $v4619004751loop->length - 1; ?><?php foreach ($v4619004751iterator as $admin) { ?><?php $v4619004751loop->first = ($v4619004751incr == 0); $v4619004751loop->index = $v4619004751incr + 1; $v4619004751loop->index0 = $v4619004751incr; $v4619004751loop->revindex = $v4619004751loop->length - $v4619004751incr; $v4619004751loop->revindex0 = $v4619004751loop->length - ($v4619004751incr + 1); $v4619004751loop->last = ($v4619004751incr == ($v4619004751loop->length - 1)); ?><?php $v4619004751iterated = true; ?>
<?php if ($v4619004751loop->first) { ?>
<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>email</th>
        <th>Role</th>
        <th></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php } ?>
    <tr>
        <td><?php echo $admin->id; ?></td>
        <td><?php echo $admin->email; ?></td>
        <td><?php echo $admin->role; ?></td>
        <td><a href="/admin/admins/edit/<?php echo $admin->id; ?>">Редактировать</a></td>
        <td><a href="/admin/admins/del/<?php echo $admin->id; ?>">Удалить</a></td>
    </tr>
    <?php if ($v4619004751loop->last) { ?>
    </tbody>
    <tbody>
    <tr>
        <td colspan="7">
            <div>
                <?php echo $this->tag->linkTo(array('products/search', 'Первая')); ?>
                <?php echo $this->tag->linkTo(array('products/search?page=' . $page->before, 'Предыдущая')); ?>
                <?php echo $this->tag->linkTo(array('products/search?page=' . $page->next, 'Следующая')); ?>
                <?php echo $this->tag->linkTo(array('products/search?page=' . $page->last, 'Последняя')); ?>
                <span class="help-inline"><?php echo $page->current; ?> из <?php echo $page->total_pages; ?></span>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<?php } ?>
<?php $v4619004751incr++; } if (!$v4619004751iterated) { ?>
В базе нет админов
<?php } ?>