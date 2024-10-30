<?php

if (!current_user_can(get_option($this->shared->get('slug') . '_juice_menu_required_capability'))) {
    wp_die(esc_attr__('You do not have sufficient permissions to access this page.'));
}

?>

<!-- process data -->

<!-- output -->

<div class="wrap">
    <?php $this->upgrade();?>

    <h2 class="h2-ila">InternalLink Audit - Settings</h2>

    <div id="daext-menu-wrapper" class="daext-clearfix">

        <div class="interlinks-container">

                <div class="daext-items-container">
                <?php if (isset($_REQUEST['success'])) : ?>
                <div id="setting-success"></div>
                <?php endif; ?>
                   <form name="frmSaveSettings" id="frmSaveSettings" action='' method='post' action="admin.php?page=<?php echo esc_attr($this->shared->get('slug')); ?>-ignore" autocomplete="off">
                    <input type="hidden" name="hidden_action" value="ila_save_settings" />
                    <table class="form-table">
                        <tbody>
                        <tr class="ila-general-settings ila-setting-row">
                            <td scope='row'><?php _e('Words to be Ignored', 'ila'); ?></td>
                            <td>
                                <?php
                                 global $wpdb;
            

                                    $lang_data = array();
                                    foreach(ila_admin::getAllIgnoreWordLists() as $words){
                                        $lang_data[] = $words;
                                    }
                                ?>
                                <textarea name="ignore_words_textarea" id='ignore_words_textarea' class='regular-text' style="float:left;" rows=10><?php echo esc_textarea(implode("\n", $lang_data));  ?></textarea>          
                                <div class="help-icon" title="Internal Link Audit will ignore these words when making linking suggestions.Please enter each word on a new line">
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class='submit'>
                        <input type='submit' name='btnsave' id='btnsave' value='<?php echo esc_attr__('Save Settings', 'ila'); ?>' class='button-primary' />
                    </p>
                </form>

                </div>
        </div>

        <div class="sidebar-container">

            <div class="daext-widget">

                            <?php
                            
                                if ( ila_fs()->is_free_plan() ) {
                                    $this->renderPromo();
                                }

                                $this->support();

                            ?>
                        </div>

        </div>

    </div>

</div>
               