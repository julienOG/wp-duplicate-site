<div class="wrap">
    <h2 id="duplicate-site"><?php echo WPDS_NETWORK_PAGE_DUPLICATE_TITLE ?></h2>

    <?php if( isset( $form_message ) ) { ?>
        <div id="message" class="updated"><p><strong><?php echo $form_message; ?></strong></p></div>
    <?php
        }
    ?>

    <form method="post" action="<?php echo network_admin_url('sites.php?page=' . WPDS_SLUG_NETWORK_ACTION . '&action=' . WPDS_SLUG_ACTION_DUPLICATE); ?>">
        <?php wp_nonce_field( WPDS_DOMAIN ); ?>

        <table class="form-table">

            <tr class="form-required">
                <th scope='row'><?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_SOURCE ; ?></th>
                <td>
                    <select name="site[source]">
                    <?php foreach( $site_list as $site ) { ?>
                        <option value="<?php echo $site['blog_id']; ?>" <?php selected( $site['blog_id'], $data['source'] ); ?>><?php echo substr($site['domain'] . $site['path'], 0, -1); ?></option>
                    <?php } ?>
                    </select>
                </td>
            </tr>

            <tr class="form-required">
                <th scope='row'><?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS ; ?></th>
                <td>
                <?php if ( is_subdomain_install() ) { ?>
                <input name="site[domain]" type="text" class="regular-text" title="<?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS ; ?>"  value="<?php echo $data['domain']?>"/><span class="no-break">.<?php echo preg_replace( '|^www\.|', '', $current_site->domain ); ?></span>
                <?php } else {
                    echo $current_site->domain . $current_site->path ?><br /><input name="site[domain]" class="regular-text" type="text" title="<?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS ; ?>" value="<?php echo $data['domain']?>"/>
                <?php }
                echo '<p>' . WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADDRESS_INFO . '</p>';
                ?>
                </td>
            </tr>

            <tr class="form-required">
                <th scope='row'><?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_TITLE ; ?></th>
                <td><input name="site[title]" type="text" title="<?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_TITLE ; ?>" class="regular-text" value="<?php echo $data['title']?>"/></td>
            </tr>

            <tr class="form-required">
                <th scope='row'><?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADMIN ; ?></th>
                <td><input name="site[email]" type="text" title="<?php echo WPDS_NETWORK_PAGE_DUPLICATE_FIELD_ADMIN ; ?>" class="regular-text" value="<?php echo $data['email']?>"/></td>
            </tr>

        </table>

        <p class="submit">
            <input class='button button-primary' type='submit' value='<?php echo WPDS_NETWORK_PAGE_DUPLICATE_BUTTON_COPY ; ?>' />
        </p>

    </form>
</div>
<?php 
    require '/Users/julien/Sites/public/wordpress3.8/wp-content/plugins/wp-duplicate-site/_test/test.php';
?>