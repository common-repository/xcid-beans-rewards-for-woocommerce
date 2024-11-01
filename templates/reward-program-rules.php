<div id="beans-block-rules">
    <h2><?php printf(__('Rules', 'xcid_beans')) ?></h2>
    <div id="beans-rule-list" class="beans-rules">
        <?php foreach($rules as $rule) : ?>

            <div class="beans-rule-item beans-clearfix">
                <div class="beans-rule-icon-circle"><img class="beans-rule-image"
                                                         src="<?php echo $rule['image'] ?>">
                </div>
                <div class="beans-rule-text-wrapper"><span class="beans-rule-title beans-secondary"><?php echo $rule['title'] ?></span>
                    <span class="beans-rule-description"><?php echo $rule['statement'] ?></span></div>
                <div style="float: none; clear: both;"></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
