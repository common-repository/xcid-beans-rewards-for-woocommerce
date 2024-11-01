<?php if ( $history !== false ) : ?>
    <div id="beans-block-history">
        <h2><?php printf( __( 'History', 'xcid_beans' ) ) ?></h2>
        <div id="beans-history-list" class="beans-history">
            <?php foreach($history as $entry) : ?>
                <div class="beans-history-entry clearfix">
                        <div class="beans-history-description"><?php echo $entry['description'] ?></div>
                        <div class="beans-history-date"><?php echo (new DateTime(date($entry['created'])))->format('d/m/Y') ?></div>
                        <div class="beans-history-beans"><?php WC()->beans->print_bean($entry['delta'], false); ?></div>
                    </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
