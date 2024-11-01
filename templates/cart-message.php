<div class="beans-cart" beans-btn-class="btn">
    <div class="beans-cart-div">
        <div class="beans-cart-info-div">
            <div class="beans-info-contain">
                <div class="beans-cart-message" beans-data="cart-message"></div>
                <?php echo $message ?>
            </div>
        </div>
        <div class="beans-cart-action-div"><input class="btn button" <?php echo $disable_button ? 'disabled' : '' ?> type="button" name="beans_<?php echo $button_action ?>" value="<?php echo $button_message ?>"></div>
    </div>
</div>