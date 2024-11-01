<div class="woocommerce-billing-fields woo-billing">

<p class="form-row form-row-first validate-required" id="billing_first_name_field">
    <label for="billing_first_name" class="">
        <?php echo __( 'First Name', 'woocommerce' ) ?> <abbr class="" title="requis">*</abbr>
    </label>

    <input type="text" pattern=".{2,}" class="input-text" name="billing_first_name" id="billing_first_name" value="<?php echo $billing_first_name; ?>" required>
</p>

<p class="form-row form-row-last validate-required" id="billing_last_name_field">
    <label for="billing_last_name" class="">
        <?php echo __( 'Last Name', 'woocommerce' ) ?> <abbr class="required" title="requis">*</abbr>
    </label>

    <input type="text" pattern=".{2,}" class="input-text" name="billing_last_name" id="billing_last_name" value="<?php echo $billing_last_name; ?>" required>
</p>
</div>
<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />