<?php
/* List data-table values, i.e: $_SERVER, $_GET, .... */

/* Remove some entries from $v->tables, the Handler doesn't let us */
unset ($tables['Server/Request Data']);
unset ($tables['POST Data']);
unset ($tables['GET Data']);
unset ($tables['Session']);
unset ($tables['Cookies']);
unset ($tables['Files']);
?>
<div class="details">
  <div class="data-table-container" id="data-tables">
    <?php foreach($tables as $label => $data): ?>
      <div class="data-table" id="sg-<?php echo $tpl->escape($tpl->slug($label)) ?>">
        <label><?php echo $tpl->escape($label) ?></label>
        <?php if(!empty($data)): ?>
            <table class="data-table">
              <thead>
                <tr>
                  <td class="data-table-k">Key</td>
                  <td class="data-table-v">Value</td>
                </tr>
              </thead>
            <?php foreach($data as $k => $value): ?>
              <tr>
                <td><?php echo $tpl->escape($k) ?></td>
                <td><?php echo $tpl->escape(print_r($value, true)) ?></td>
              </tr>
            <?php endforeach ?>
            </table>
        <?php else: ?>
          <span class="empty">empty</span>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>

</div>
