<?php if($join) : ?>
<div id="beans-block-intro">
    <div class="beans-intro beans-clearfix">
        <a id="beans-intro-cta" class="beans-intro-cta beans-primary-bg" href="<?php echo $signup_link ?>"><?php printf( __('Join now', 'xcid_beans')) ?></a>
        <div class="beans-intro-content">
            <?php printf(__('Join our customer reward program and get <span id="beans-if-join">%s</span>', 'xcid_beans'), $beans) ?>
            <?php printf(__('that you can spend now in our shop or save for a later purchase.')) ?>
        </div>
    </div>
</div>
<?php endif; ?>
