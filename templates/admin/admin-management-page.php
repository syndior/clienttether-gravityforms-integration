<?php 

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <form method="post" action="options.php">
        <?php settings_fields( CTGF_API_ADMIN_PAGE );?>
        <?php do_settings_sections( CTGF_API_ADMIN_PAGE );?>
        <?php submit_button();?>
    </form>
</div>