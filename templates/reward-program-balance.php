<?php if($balance !== false) : ?>
<div id="beans-block-balance">
    <h2><?php printf( __( 'Balance', 'xcid_beans' ) ) ?></h2>
    <div class="beans-balance beans-primary-bg" id="beans-balance"><span id="beans-account-balance"><?php echo $balance?> <?php echo $beans_name ?></span></div>
</div>
<?php endif; ?>