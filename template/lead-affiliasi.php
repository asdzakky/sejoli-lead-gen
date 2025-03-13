<?php sejoli_header(); ?>

<h2 class="ui header"><?php _e('Link Affiliasi', 'sejoli-lead-form'); ?></h2>

<form id="affiliate-link-generator" class="ui form">
    <div class="ui fluid action input">
        <select id="lead_form_id" name="lead_form_id" class="ui fluid dropdown">
            <option value=""><?php _e( 'Tunggu sebentar... kami sedang mengambil semua data form lead', 'sejoli-lead-form' ); ?></option>
        </select>
        <button id="lead-affiliate-link-generator-button" class="ui primary button">
            <?php _e( 'Generate', 'sejoli-lead-form' ); ?>
        </button>
    </div>
</form><br>
<div id="affiliate-link-holder">
    <div class="ui info message"><?php _e( 'Silahkan pilih Form Lead', 'sejoli-lead-form' ); ?></div>
</div>
<div class="loading" style="display: none;"><?php _e( 'Please Wait...', 'sejoli-lead-form' ); ?></div>

<script id="affiliate-link-tmpl" type="text/x-jsrender">
{{props data}}
    <div class='field'>
        <label for="aff-link-{{:key}}"><b>{{:prop.label}}</b></label>
        <p>{{:prop.description}}</p>
        <div class="ui fluid action input">
            <input id="aff-link-{{:key}}" name="aff-link-{{:key}}" type="text" value="{{:prop.affiliate_link}}" readonly>
            <button class="ui teal right labeled icon button copy-btn" data-clipboard-target="#aff-link-{{:key}}"><i class="copy icon"></i> <?php _e( 'Copy', 'sejoli-lead-form' ); ?></button>
        </div>
    </div>
{{/props}}
</script>

<?php sejoli_footer();