<?php

class UGM_Blocks_Importer {

	private $templates_path = UGM_THEME_DIR . '/block-templates';

	private $blocks_version = 2;

	public function __construct() {
		$installed_ver = get_option( 'ugm_theme_blocks_ver', 0 );
		if ( $installed_ver < $this->blocks_version ) {
			add_action( 'init', array( $this, 'scan_templates' ) );
		}
	}

	public function scan_templates() {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once  ABSPATH . '/wp-admin/includes/file.php' ;
			WP_Filesystem();
		}
		$template_files = scandir( $this->templates_path, 1 );
		if ( false === $template_files || ! is_array( $template_files ) ) {
			return $this->not_writable_alert();
		}
		// remove unecessary dir results.
		$template_files = array_diff( $template_files, array('.', '..') );
		// reset array indexes.
		$template_files = array_values( $template_files );
		asort( $template_files );

		$successful_import = array();
		foreach ( $template_files as $key => $file ) {
			$json = file_get_contents( $this->templates_path . '/' . $file );
			if ( false === $json ) {
				continue;
			}

			$json = json_decode( $json );
			$post = get_page_by_title( $json->title, OBJECT, $json->__file );
			if ( $post ) {
				if ( isset( $json->ver ) ) {
					$ver = get_post_meta( $post->ID, 'blocks_version', true );
					if ( intval( $ver ) < intval( $json->ver ) ) {
						if ( $this->do_import( $json, $post->ID ) ) {
							$successful_import[] = $key;
						}
						continue;
					}
				}
				$successful_import[] = $key;
				continue;
			}
			if ( $this->do_import( $json ) ) {
				$successful_import[] = $key;
			}
		}
		if ( count( $successful_import ) === count( $template_files ) ) {
			update_option( 'ugm_theme_blocks_ver', $this->blocks_version );
		}
	}

	private function do_import( $json, $post_id = 0 ) {
		if ( ! empty( $post_id ) ) {
			$return = wp_update_post( array(
				'ID' => $post_id,
				'post_type' => $json->__file,
				'post_title' => $json->title,
				'post_content' => $json->content,
				'post_status' => 'publish'
			) );
		} else {
			$return = wp_insert_post( array(
				'post_type' => $json->__file,
				'post_title' => $json->title,
				'post_content' => $json->content,
				'post_status' => 'publish'
			) );
		}
		if ( ! is_wp_error( $return ) ) {
			if ( isset( $json->ver ) ) {
				update_post_meta( $return, 'blocks_version', $json->ver );
			}
			return true;
		}
		return false;
	}

	private function not_writable_alert() {
	    ?>
	    <div class="notice notice-error">
	        <p><?php _e( 'We unable to access <strong>' . $this->templates_path . '</strong>, please make sure that path si writeable.', 'ugm-theme' ); ?></p>
	    </div>
	    <?php
	}

}
new UGM_Blocks_Importer();