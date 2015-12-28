<?php $v115050340928910814031iterated = false; ?><?php $v115050340928910814031iterator = $page->items; $v115050340928910814031incr = 0; $v115050340928910814031loop = new stdClass(); $v115050340928910814031loop->length = count($v115050340928910814031iterator); $v115050340928910814031loop->index = 1; $v115050340928910814031loop->index0 = 1; $v115050340928910814031loop->revindex = $v115050340928910814031loop->length; $v115050340928910814031loop->revindex0 = $v115050340928910814031loop->length - 1; ?><?php foreach ($v115050340928910814031iterator as $admin) { ?><?php $v115050340928910814031loop->first = ($v115050340928910814031incr == 0); $v115050340928910814031loop->index = $v115050340928910814031incr + 1; $v115050340928910814031loop->index0 = $v115050340928910814031incr; $v115050340928910814031loop->revindex = $v115050340928910814031loop->length - $v115050340928910814031incr; $v115050340928910814031loop->revindex0 = $v115050340928910814031loop->length - ($v115050340928910814031incr + 1); $v115050340928910814031loop->last = ($v115050340928910814031incr == ($v115050340928910814031loop->length - 1)); ?><?php $v115050340928910814031iterated = true; ?>
<?php if ($v115050340928910814031loop->first) { ?>
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
    <?php if ($v115050340928910814031loop->last) { ?>
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
<?php $v115050340928910814031incr++; } if (!$v115050340928910814031iterated) { ?>
В базе нет админов
<?php } ?>