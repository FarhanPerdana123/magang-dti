<?php

require_once UGM_THEME_DIR_INC . '/class-ugm-nav-menu-item-custom-fields.php';

use ScssPhp\ScssPhp\Compiler;
use MatthiasMullie\Minify;

class UGM_Hooks_Admin {

	public function __construct() {
		// add_action( 'init', array( 'UGM_Nav_Menu_Item_Custom_Fields', 'setup' ) );

		add_action( 'show_user_profile', [ $this, 'show_user_social_links' ] );
		add_action( 'edit_user_profile', [ $this, 'show_user_social_links' ] );
		add_action( 'personal_options_update', [ $this, 'save_user_social_links' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_user_social_links' ] );
	}

	public function show_user_social_links() {
		?>
	    <h3><?php _e( 'Sosial Media', 'ugm-theme' ); ?></h3>
	    <table class="form-table">
	    	<?php
	    		if ( ! empty( $social_list ) ) :
	    			foreach ( $social_list as $key => $social ) :
	    	?>
	        <tr>
	            <th>
	            	<label for="<?php echo $social['meta']; ?>">
	            		<?php echo $social['label']; ?>
	                </label>
	            </th>
	            <td>
	            	<input type="url" name="<?php echo $social['meta']; ?>" value="<?php echo esc_attr( get_the_author_meta( $social['meta'], $user->ID  ) ); ?>" class="regular-text" />
	            </td>
	        </tr>
	        <?php
	        	 	endforeach;
	        	endif;
	        ?>
	    </table>
		<?php
	}

	public function save_user_social_links() {
		$profile_text = __( 'Profil', 'ugm-theme' );

		$social_list = array(
    		'facebook' => array(
    			'label'	=> $profile_text . ' Facebook',
    			'meta'	=> 'ugm_user_facebook',
    			'class'	=> 'regular-text'
    		),
    		'twitter' => array(
    			'label'	=> $profile_text . ' Twitter',
    			'meta'	=> 'ugm_user_twitter',
    			'class'	=> 'regular-text'
    		),
    		'google' => array(
    			'label'	=> $profile_text . ' Google',
    			'meta'	=> 'ugm_user_google',
    			'class'	=> 'regular-text'
    		),
    		'instagram' => array(
    			'label'	=> $profile_text . ' Instagram',
    			'meta'	=> 'ugm_user_instagram',
    			'class'	=> 'regular-text'
    		),
    		'email' => array(
    			'label'	=> __( 'Email', 'ugm-theme' ),
    			'meta'	=> 'ugm_user_email',
    			'class'	=> 'regular-text'
    		)
    	);

    	$social_list = apply_filters( 'ugm_social_list', $social_list );

    	foreach ( $social_list as $key => $social ) {
		    update_user_meta( $user_id, $social['meta'], sanitize_text_field( $_POST[$social['meta']] ) );
    	}
	}

}
new UGM_Hooks_Admin();