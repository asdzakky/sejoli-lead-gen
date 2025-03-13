<?php ob_start(); ?>

<div class="leadform-show-form-<?php echo $this_form_id; ?> leadform-show-form <?php echo $this_form_size; ?> lf-form-default leadform-lite"><div class="lead-head"></div><?php echo $captcha_script; ?>
<form action="" method="post" class="lead-form-front" id="form_<?php echo $this_form_id; ?>" enctype="multipart/form-data">
<?php echo $show_title . $form_elemets; ?><div class="lf-form-panel"><?php echo $submit_button; ?></div>
<div class="captcha-field-area" id="captcha-field-area"></div>
<input type="hidden" class="hidden_field" name="hidden_field" value="<?php echo $this_form_id; ?>"/>
<input type="hidden" class="form_title" name="form_title" value="<?php echo $form_title; ?>"/>
<input type="hidden" class="product_id" name="product" value="<?php echo $form_product; ?>"/>
<input type="hidden" class="affiliate_id" name="affiliate_id" value="<?php echo $affiliate_id; ?>"/>
<input type="hidden" class="this_form_captcha_status" value="<?php echo $captcha_status; ?>"/>
<div class="leadform-show-loading front-loading leadform-show-message-form-<?php echo $this_form_id; ?>" >
</div>
<div class="lf-loading">
    <div class="spinner" id="loading_image" style="display: none;" wppb-add-style="display:none;">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
    </div>
</div>
</form>
<p wppb-add-style="display:none;" style="display:none;" class="errormsg_<?php echo intval($this_form_id); ?> errormsg"><?php echo esc_html($error_msg); ?></p>
<p wppb-add-style="display:none;" style="display:none;" redirect="<?php echo esc_url($redirect_url); ?>" class="infomsg_<?php echo intval($this_form_id); ?> infomsg"><?php echo esc_html($success_msg); ?></p>
<p wppb-add-style="display:none;" style="display:none;" redirect="<?php echo esc_url($redirect_url); ?>" class="successmsg_<?php echo intval($this_form_id); ?> successmsg"><?php echo esc_html($success_msg); ?></p><?php echo $show_affiliate; ?>

<?php
$html = ob_get_contents();
ob_end_clean();
?>  