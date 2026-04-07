<?php

class UGM_Custom_Fields {

	public $palletes = array();

	public function __construct() {
		global $wp_version;
		// add_action( 'acf/init', [ $this, 'load_theme_json' ] );
		add_action( 'acf/init', [ $this, 'setup' ] );
		add_action( 'acf/init', [ $this, 'load_fields' ] );
		add_filter( 'acf/settings/path', [ $this, 'acf_settings_path' ] );
		add_filter( 'acf/settings/dir', [ $this, 'acf_settings_dir' ] );
		add_filter( 'acf/settings/show_admin', '__return_false' );

		// add_filter( 'acf/prepare_field/name=ugm_options_look_color', array( $this, 'theme_options_load_colors' ) );
		if ( 0 < version_compare( $wp_version, '6.1.0' ) ) {
			add_filter( 'wp_theme_json_data_theme', [ $this, 'modify_theme_json' ] );
		} else {
			add_action( 'after_setup_theme', [ $this, 'modify_theme_support' ] );
		}
	}

	public function load_theme_json() {
		if ( is_admin() ) {
			$theme_json = file_get_contents( UGM_THEME_DIR . '/theme.json');
			$theme_json = json_decode( $theme_json );
			$this->palletes = $theme_json->settings->color->palette;
		}
	}

	public function theme_options_load_colors( $field ) {
		if ( false !== $field['value'] ) {
			return $field;
		}
		$field['value'] = array();
		foreach ( $this->palletes as $key => $pallete ) {
			$field['value'][0][ array_keys( $field['value'][0] )[ $key ] ] = $pallete->color;
		}
		return $field;
	}

	public function modify_theme_support() {
		$color = get_field( 'ugm_options_look_color', 'option' );
		if ( is_array( $color ) && ! empty( $color ) ) {
			add_theme_support( 'editor-color-palette', array(
				array(
					"name" => "Primary",
					"slug" => "ugm-primary",
					"color" => $color[0]['ugm-primary']
				),
				array(
					"name" => "Secondary",
					"slug" => "ugm-secondary",
					"color" => $color[0]['ugm-secondary']
				),
				array(
					"name" => "Tertiary",
					"slug" => "ugm-tertiary",
					"color" => $color[0]['ugm-tertiary']
				),
				array(
					"name" => "Primary Background",
					"slug" => "ugm-primary-bg",
					"color" => $color[0]['ugm-primary-bg']
				),
				array(
					"name" => "Secondary Background",
					"slug" => "ugm-secondary-bg",
					"color" => $color[0]['ugm-secondary-bg']
				)
			) );
		} else {
			add_theme_support( 'editor-color-palette', array(
				array(
					"name" => "Primary",
					"slug" => "ugm-primary",
					"color" => '#1A2D42'
				),
				array(
					"name" => "Secondary",
					"slug" => "ugm-secondary",
					"color" => '#fdcb2c'
				),
				array(
					"name" => "Tertiary",
					"slug" => "ugm-tertiary",
					"color" => '#149fc0'
				),
				array(
					"name" => "Primary Background",
					"slug" => "ugm-primary-bg",
					"color" => '#083d62'
				),
				array(
					"name" => "Secondary Background",
					"slug" => "ugm-secondary-bg",
					"color" => '#FAFAFA'
				)
			) );
		}
	}

	public function modify_theme_json( $theme_json ) {
		$font  = get_field( 'ugm_options_look_fonts', 'option' );
		$color = get_field( 'ugm_options_look_color', 'option' );
		$new_data = array(
			'version'  => 2,
			'settings' => array()
		);

		if ( ! empty( $font ) ) {
			if ( 'albertsans-albertsans' == $font ) {
				$new_data['settings']['typography'] = array(
					'fontFamilies' => array(
						array(
							"fontFamily" => "\"Albert Sans\",Helvetica,Arial,sans-serif",
		                    "slug" => "heading-font",
		                    "name" => "Heading Font"
						),
						array(
							"fontFamily" => "\"Albert Sans\",Helvetica,Arial,sans-serif",
		                    "slug" => "content-font",
		                    "name" => "Content Font"
						)
					)
				);
			} elseif ( 'lora-opensans' == $font ) {
				$new_data['settings']['typography'] = array(
					'fontFamilies' => array(
						array(
							"fontFamily" => "Lora,Georgia,\"Times New Roman\",serif",
		                    "slug" => "heading-font",
		                    "name" => "Heading Font"
						),
						array(
							"fontFamily" => "\"Open Sans\",Helvetica,Arial,sans-serif",
		                    "slug" => "content-font",
		                    "name" => "Content Font"
						)
					)
				);
			} elseif ( 'circularstd-circularstd' == $font ) {
				$new_data['settings']['typography'] = array(
					'fontFamilies' => array(
						array(
							"fontFamily" => "\"Circular Std\",Helvetica,Arial,sans-serif",
		                    "slug" => "heading-font",
		                    "name" => "Heading Font"
						),
						array(
							"fontFamily" => "\"Circular Std\",Helvetica,Arial,sans-serif",
		                    "slug" => "content-font",
		                    "name" => "Content Font"
						)
					)
				);
			} elseif ( 'proximanova-circularstd' == $font ) {
				$new_data['settings']['typography'] = array(
					'fontFamilies' => array(
						array(
							"fontFamily" => "\"Proxima Nova\",Helvetica,Arial,sans-serif",
		                    "slug" => "heading-font",
		                    "name" => "Heading Font"
						),
						array(
							"fontFamily" => "\"Circular Std\",Helvetica,Arial,sans-serif",
		                    "slug" => "content-font",
		                    "name" => "Content Font"
						)
					)
				);
			}
		}

		if ( is_array( $color ) && ! empty( $color ) ) {
			$new_data['settings']['color'] = array(
				'palette' => array(
					array(
						"name" => "Primary",
						"slug" => "ugm-primary",
						"color" => $color[0]['ugm-primary']
					),
					array(
						"name" => "Secondary",
						"slug" => "ugm-secondary",
						"color" => $color[0]['ugm-secondary']
					),
					array(
						"name" => "Tertiary",
						"slug" => "ugm-tertiary",
						"color" => $color[0]['ugm-tertiary']
					),
					array(
						"name" => "Primary Background",
						"slug" => "ugm-primary-bg",
						"color" => $color[0]['ugm-primary-bg']
					),
					array(
						"name" => "Secondary Background",
						"slug" => "ugm-secondary-bg",
						"color" => $color[0]['ugm-secondary-bg']
					)
				)
			);
		} else {
			$new_data['settings']['color'] = array(
				'palette' => array(
					array(
						"name" => "Primary",
						"slug" => "ugm-primary",
						"color" => '#1A2D42'
					),
					array(
						"name" => "Secondary",
						"slug" => "ugm-secondary",
						"color" => '#fdcb2c'
					),
					array(
						"name" => "Tertiary",
						"slug" => "ugm-tertiary",
						"color" => '#149fc0'
					),
					array(
						"name" => "Primary Background",
						"slug" => "ugm-primary-bg",
						"color" => '#083d62'
					),
					array(
						"name" => "Secondary Background",
						"slug" => "ugm-secondary-bg",
						"color" => '#FAFAFA'
					)
				)
			);
		}

		if ( ! empty( $new_data['settings'] ) ) {
			$theme_json->update_with( $new_data );
		}
		return $theme_json;
	}

	public function setup() {
		acf_update_setting( 'google_api_key', 'AIzaSyBI5QKXI5IRonR4hGhQt3w3s6AMsVbP7EY' );
		acf_add_options_page(array(
			'page_title' 	=> 'Pengaturan Tampilan',
			'menu_title'	=> 'Pengaturan Tampilan',
			'menu_slug' 	=> 'theme-options',
			'capability'	=> 'edit_posts',
			'redirect'		=> false,
			'icon_url'		=> 'dashicons-feedback',
			'position'		=> 61
		));
	}

	public function load_fields() {
		acf_add_local_field_group(array (
			'key' => 'group_57fdb0a18d8ea',
			'title' => 'Detail Event',
			'fields' => array (
				array (
					'key' => 'field_57fdb0cf71e14',
					'label' => 'Tanggal',
					'name' => 'ugm_event_date',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'd/m/Y',
					'return_format' => 'Y/m/d',
					'first_day' => 1,
				),
				array (
					'key' => 'field_57fdf11dab344',
					'label' => 'Tanggal Selesai',
					'name' => 'ugm_event_date_end',
					'type' => 'date_picker',
					'instructions' => 'kosongi jika acara hanya sehari',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'd/m/Y',
					'return_format' => 'Y/m/d',
					'first_day' => 1,
				),
				array (
					'key' => 'field_57fdf1aaab347',
					'label' => 'Penyelenggara',
					'name' => 'ugm_event_comitee',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57fdf1cfab348',
					'label' => 'Lokasi',
					'name' => 'ugm_event_location',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 3,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57fdf1ebab349',
					'label' => 'Kontak',
					'name' => 'ugm_event_contact',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 3,
					'new_lines' => 'br',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57fdf1feab34a',
					'label' => 'Situs Web',
					'name' => 'ugm_event_website',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57fdf32fc581c',
					'label' => 'Deskripsi Singkat',
					'name' => 'ugm_event_short_description',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 3,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_580991c6f2dc8',
			'title' => 'Direktori 1 Kolom',
			'fields' => array (
				array (
					'key' => 'field_5822b08216ade',
					'label' => 'Tampilkan Gambar?',
					'name' => 'ugm_directory_show_image',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
				),
				array (
					'key' => 'field_5822b12116adf',
					'label' => 'Bilah Sisi',
					'name' => 'ugm_directory_sidebar',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'none' => 'Tanpa Sidebar',
						'menu' => 'Menu',
						'html' => 'Konten HTML',
					),
					'default_value' => array (
						'none' => 'none',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_580991ce8dc62',
					'label' => 'Daftar Direktori',
					'name' => 'ugm_directory_list',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'layout' => 'row',
					'button_label' => 'Tambah Direktori',
					'sub_fields' => array (
						array (
							'key' => 'field_580992868dc63',
							'label' => 'Judul',
							'name' => 'title',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_5809933e8dc64',
							'label' => 'Deskripsi',
							'name' => 'description',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 3,
							'new_lines' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_5822b17a16ae0',
							'label' => 'Gambar',
							'name' => 'image',
							'type' => 'image',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array (
								array (
									array (
										'field' => 'field_5822b08216ade',
										'operator' => '==',
										'value' => '1',
									),
								),
							),
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'id',
							'preview_size' => 'thumbnail',
							'library' => 'all',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
						array (
							'key' => 'field_580993538dc65',
							'label' => 'Teks Tombol',
							'name' => 'button_text',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => 'Kunjungi',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_580993638dc66',
							'label' => 'Link Tombol',
							'name' => 'button_link',
							'type' => 'url',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
						),
						array (
							'key' => 'field_580993948dc67',
							'label' => 'Sub Direktori',
							'name' => 'sub_directory',
							'type' => 'repeater',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'min' => '',
							'max' => '',
							'layout' => 'table',
							'button_label' => 'Tambah Sub Direktori',
							'sub_fields' => array (
								array (
									'key' => 'field_580993a18dc68',
									'label' => 'Judul',
									'name' => 'title',
									'type' => 'text',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'default_value' => '',
									'placeholder' => '',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
									'readonly' => 0,
									'disabled' => 0,
								),
								array (
									'key' => 'field_580993ac8dc69',
									'label' => 'Hyperlink',
									'name' => 'hyperlink',
									'type' => 'url',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'default_value' => '',
									'placeholder' => '',
								),
							),
						),
					),
				),
				array (
					'key' => 'field_5822b1e616ae1',
					'label' => 'Konten HTML',
					'name' => 'ugm_directory_html_content',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_5822b12116adf',
								'operator' => '==',
								'value' => 'html',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/directory-single.php',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
				0 => 'the_content',
				1 => 'excerpt',
				2 => 'discussion',
				3 => 'comments',
				4 => 'author',
				5 => 'format',
				6 => 'featured_image',
				7 => 'categories',
				8 => 'tags',
				9 => 'send-trackbacks',
			),
		));

		acf_add_local_field_group(array (
			'key' => 'group_5822fb142598d',
			'title' => 'Direktori 2 Kolom',
			'fields' => array (
				array (
					'key' => 'field_5822fb144d822',
					'label' => 'Bilah Sisi',
					'name' => 'ugm_directory_sidebar',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'none' => 'Tanpa Sidebar',
						'menu' => 'Menu',
						'html' => 'Konten HTML',
					),
					'default_value' => array (
						'none' => 'none',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_5822fb144da15',
					'label' => 'Daftar Direktori',
					'name' => 'ugm_directory_list',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'layout' => 'row',
					'button_label' => 'Tambah Direktori',
					'sub_fields' => array (
						array (
							'key' => 'field_5822fb14bcac1',
							'label' => 'Judul',
							'name' => 'title',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_5822fb14bcc96',
							'label' => 'Deskripsi',
							'name' => 'description',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 3,
							'new_lines' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_5822fb14bd093',
							'label' => 'Teks Tombol',
							'name' => 'button_text',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => 'Kunjungi',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_5822fb14bd27b',
							'label' => 'Link Tombol',
							'name' => 'button_link',
							'type' => 'url',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
						),
						array (
							'key' => 'field_5822fb14bd491',
							'label' => 'Sub Direktori',
							'name' => 'sub_directory',
							'type' => 'repeater',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'min' => '',
							'max' => '',
							'layout' => 'table',
							'button_label' => 'Tambah Sub Direktori',
							'sub_fields' => array (
								array (
									'key' => 'field_5822fb15f2de9',
									'label' => 'Judul',
									'name' => 'title',
									'type' => 'text',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'default_value' => '',
									'placeholder' => '',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
									'readonly' => 0,
									'disabled' => 0,
								),
								array (
									'key' => 'field_5822fb15f2fd5',
									'label' => 'Hyperlink',
									'name' => 'hyperlink',
									'type' => 'url',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'default_value' => '',
									'placeholder' => '',
								),
							),
						),
					),
				),
				array (
					'key' => 'field_5822fb144dbfe',
					'label' => 'Konten HTML',
					'name' => 'ugm_directory_html_content',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_5822fb144d822',
								'operator' => '==',
								'value' => 'html',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/directory-double.php',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
				0 => 'the_content',
				1 => 'excerpt',
				2 => 'discussion',
				3 => 'comments',
				4 => 'author',
				5 => 'format',
				6 => 'featured_image',
				7 => 'categories',
				8 => 'tags',
				9 => 'send-trackbacks',
			),
		));

		acf_add_local_field_group(array (
			'key' => 'group_581aac95201bc',
			'title' => 'Favicon',
			'fields' => array (
				array (
					'key' => 'field_581aac9b87271',
					'label' => 'Favicon',
					'name' => 'ugm_options_favicon',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'url',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => 64,
					'max_height' => 64,
					'max_size' => '',
					'mime_types' => 'png, gif, ico, jpg',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_585b546875398',
			'title' => 'Files',
			'fields' => array (
				array (
					'key' => 'field_585b54777545a',
					'label' => 'File',
					'name' => 'ugm_post_file',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => 1,
					'max' => '',
					'layout' => 'table',
					'button_label' => 'Add File',
					'sub_fields' => array (
						array (
							'key' => 'field_585b55367545b',
							'label' => 'File',
							'name' => 'file',
							'type' => 'file',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'array',
							'library' => 'all',
							'min_size' => '',
							'max_size' => '',
							'mime_types' => '',
						),
					),
				),
				array (
					'key' => 'field_585b67a630756',
					'label' => 'Tampilkan Pratinjau',
					'name' => 'ugm_post_file_preview',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'file',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_5809e195ece7f',
			'title' => 'Gallery',
			'fields' => array (
				array (
					'key' => 'field_5809e1a4b6f0d',
					'label' => 'Foto',
					'name' => 'ugm_gallery',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'layout' => 'row',
					'button_label' => 'Tambah Foto',
					'sub_fields' => array (
						array (
							'key' => 'field_5809e1c3b6f0e',
							'label' => 'Foto',
							'name' => 'image',
							'type' => 'image',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'id',
							'preview_size' => 'thumbnail',
							'library' => 'all',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
						array (
							'key' => 'field_5809e1dcb6f0f',
							'label' => 'Judul',
							'name' => 'caption',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_580f21cb3dbd4',
							'label' => 'Deskripsi',
							'name' => 'description',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 3,
							'new_lines' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'gallery',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
				0 => 'the_content',
				1 => 'format',
				2 => 'page_attributes',
			),
		));

		acf_add_local_field_group(array (
			'key' => 'group_57ff07e2774d0',
			'title' => 'Header',
			'fields' => array (
				array (
					'key' => 'field_58196585e2497',
					'label' => 'Model Header',
					'name' => 'ugm_options_header_type',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'default' => 'Background gelap. Menu disamping logo.',
						'below' => 'Background gelap. Menu dibawah logo.',
						'default_burger' => 'Background gelap. Menu burger.',
						'light' => 'Background putih.',
						'light_burger' => 'Background putih. Menu burger.'
					),
					'default_value' => array (
						'default' => 'default',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_57ff07ecfecb6',
					'label' => 'Logo',
					'name' => 'ugm_options_header_logo',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'url',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => 'png',
				),
				array (
					'key' => 'field_57ff1514fecb7',
					'label' => 'Teks Logo',
					'name' => 'ugm_options_header_text',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 'Universitas Gadjah Mada',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_584a821695ecb',
					'label' => 'Teks Logo (english)',
					'name' => 'ugm_options_header_text_en',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57ff1584fecb8',
					'label' => 'Sub Teks Logo',
					'name' => 'ugm_options_header_sub_text',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 2,
					'new_lines' => 'br',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_584a823a95ecc',
					'label' => 'Sub Teks Logo (english)',
					'name' => 'ugm_options_header_sub_text_en',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 2,
					'new_lines' => 'br',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_57ff15adfecb9',
					'label' => 'Kolom Pencarian',
					'name' => 'ugm_options_header_search_field',
					'type' => 'true_false',
					'instructions' => 'Tampilkan kolom pencarian pada header?',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_5809c4d0583b6',
			'title' => 'Kontak',
			'fields' => array (
				array (
					'key' => 'field_5809c54b82075',
					'label' => 'Form',
					'name' => 'ugm_contact_form',
					'type' => 'cf7',
					'instructions' => 'buat form menggunakan plugin Contact Form 7',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'disable' => array (
						0 => 0,
					),
				),
				array (
					'key' => 'field_580dde6ed1ca6',
					'label' => 'Alamat',
					'name' => 'ugm_contact_address',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 3,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_580dde8dd1ca7',
					'label' => 'Kontak',
					'name' => 'ugm_contact_contact',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'layout' => 'table',
					'button_label' => 'Tambah Kontak',
					'sub_fields' => array (
						array (
							'key' => 'field_580ddeadd1ca8',
							'label' => 'Tipe',
							'name' => 'type',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array (
								'email' => 'Email',
								'phone' => 'Telepon',
								'fax' => 'Faximile',
							),
							'default_value' => array (
								'phone' => 'phone',
							),
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'ajax' => 0,
							'placeholder' => '',
							'disabled' => 0,
							'readonly' => 0,
						),
						array (
							'key' => 'field_580ddef3d1ca9',
							'label' => 'Nomor/Alamat',
							'name' => 'number',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
					),
				),
				array (
					'key' => 'field_5809c59282076',
					'label' => 'Peta',
					'name' => 'ugm_contact_coordinates',
					'type' => 'google_map',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'center_lat' => '-7.771385',
					'center_lng' => '110.3775',
					'zoom' => '',
					'height' => '',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/contact-page.php',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
				0 => 'the_content',
				1 => 'excerpt',
				2 => 'discussion',
				3 => 'comments',
				4 => 'revisions',
				5 => 'author',
				6 => 'format',
				7 => 'featured_image',
				8 => 'categories',
				9 => 'tags',
				10 => 'send-trackbacks',
			),
		));

		acf_add_local_field_group(array (
			'key' => 'group_5824374e48c28',
			'title' => 'Layout Halaman',
			'fields' => array (
				array (
					'key' => 'field_5824375b8ac90',
					'label' => 'Tata Letak',
					'name' => 'ugm_options_page_layout',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'default' => 'Sesuai Tema',
						'right' => 'Sidebar Kanan',
						'left' => 'Sidebar Kiri',
						'none' => 'Tanpa Sidebar',
						'left-right' => '3 Kolom',
					),
					'default_value' => array (
						'default' => 'default',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/blank-page.php',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/contact-page.php',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/directory-double.php',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/directory-single.php',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/profile-page.php',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_58099967a98ec',
			'title' => 'Profile',
			'fields' => array (
				array (
					'key' => 'field_5809997e8f45d',
					'label' => 'Profil',
					'name' => 'ugm_profile_list',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'layout' => 'table',
					'button_label' => 'Tambah Profil',
					'sub_fields' => array (
						array (
							'key' => 'field_58099a238f45e',
							'label' => 'Foto',
							'name' => 'image',
							'type' => 'image',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'return_format' => 'id',
							'preview_size' => 'thumbnail',
							'library' => 'all',
							'min_width' => '',
							'min_height' => '',
							'min_size' => '',
							'max_width' => '',
							'max_height' => '',
							'max_size' => '',
							'mime_types' => '',
						),
						array (
							'key' => 'field_58099a388f45f',
							'label' => 'Nama',
							'name' => 'name',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 2,
							'new_lines' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_58099a468f460',
							'label' => 'Jabatan',
							'name' => 'position',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 2,
							'new_lines' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'page-templates/profile-page.php',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
				0 => 'the_content',
				1 => 'excerpt',
				2 => 'discussion',
				3 => 'comments',
				4 => 'revisions',
				5 => 'author',
				6 => 'format',
				7 => 'featured_image',
				8 => 'categories',
				9 => 'tags',
				10 => 'send-trackbacks',
			),
		));

		acf_add_local_field_group(array (
			'key' => 'group_57ff17030b5a8',
			'title' => 'Tampilan',
			'fields' => array (
				array (
					'key' => 'field_580d781qwerty',
					'label' => 'Font',
					'name' => 'ugm_options_look_fonts',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'lora-opensans' => 'Lora - Open Sans',
						'albertsans-albertsans' => 'Albert Sans - Albert Sans',
						'circularstd-circularstd' => 'Circular Std - Circular Std',
						'proximanova-circularstd' => 'Proxima Nova - Circular Std',
					),
					'default_value' => array (
						'albertsans-albertsans' => 'Albert Sans - Albert Sans',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_57ff176c9fe5c',
					'label' => 'Warna',
					'name' => 'ugm_options_look_color',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => 1,
					'max' => 1,
					'layout' => 'row',
					'button_label' => 'Add Row',
					'sub_fields' => array (
						array (
							'key' => 'field_581ae43b0f6de',
							'label' => 'Primary',
							'name' => 'ugm-primary',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '#1A2D42',
						),
						array (
							'key' => 'field_581ae45e0f6df',
							'label' => 'Secondary',
							'name' => 'ugm-secondary',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '#FDCB2C',
						),
						array (
							'key' => 'field_581ae4800f6e0',
							'label' => 'Tertiary',
							'name' => 'ugm-tertiary',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '#149fc0',
						),
						array (
							'key' => 'field_581ae49d0f6e1',
							'label' => 'Primary Background',
							'name' => 'ugm-primary-bg',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '#083D62',
						),
						array (
							'key' => 'field_581ae4b60f6e2',
							'label' => 'Secondary Background',
							'name' => 'ugm-secondary-bg',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '#FAFAFA',
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 2,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_57ff1703zxcvb',
			'title' => 'Pusat Bantuan',
			'fields' => array (
				array (
					'key' => 'field_57ff15adfhjkl',
					'label' => 'Tampilkan Pusat Bantuan?',
					'name' => 'help_center_show',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
				),
				array(
					'key' => 'field_63919b7bbe3c5',
					'label' => 'Judul',
					'name' => 'help_center_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_63919b83be3c6',
					'label' => 'List URL Bantuan',
					'name' => 'help_center_links',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'collapsed' => '',
					'min' => 0,
					'max' => 0,
					'layout' => 'table',
					'button_label' => '',
					'sub_fields' => array(
						array(
							'key' => 'field_63919ba5be3c8',
							'label' => 'Judul',
							'name' => 'title',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array(
							'key' => 'field_63919baebe3c9',
							'label' => 'URL',
							'name' => 'url',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
					),
				),
				// array(
				// 	'key' => 'field_6392d5c2cc415',
				// 	'label' => 'Accept Cookies	Teks ID',
				// 	'name' => 'accept_cookies_teks_id',
				// 	'type' => 'text',
				// 	'instructions' => '',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array(
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'default_value' => 'Kami menggunakan cookie untuk membantu pengunjung kami mendapatkan pengalaman terbaik di situs web kami.',
				// 	'placeholder' => '',
				// 	'prepend' => '',
				// 	'append' => '',
				// 	'maxlength' => '',
				// ),
				// array(
				// 	'key' => 'field_6392d607cc416',
				// 	'label' => 'Accept Cookies Teks EN',
				// 	'name' => 'accept_cookies_teks_en',
				// 	'type' => 'text',
				// 	'instructions' => '',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array(
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'default_value' => 'We use cookies to help our viewer get the best experience on our website.',
				// 	'placeholder' => '',
				// 	'prepend' => '',
				// 	'append' => '',
				// 	'maxlength' => '',
				// ),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 2,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_57ff191491769',
			'title' => 'Footer',
			'fields' => array (
				array (
					'key' => 'field_57ff191a40db5',
					'label' => 'Logo',
					'name' => 'ugm_options_footer_logo',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'url',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => 'png',
				),
				array (
					'key' => 'field_57ff194340db6',
					'label' => 'Teks',
					'name' => 'ugm_options_footer_text',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_584a8289672ea',
					'label' => 'Teks (english)',
					'name' => 'ugm_options_footer_text_en',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_58130bfdf1e15',
					'label' => 'Teks Hak Cipta',
					'name' => 'ugm_options_footer_copyright',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 'Universitas Gajah Mada',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_584a8297672eb',
					'label' => 'Teks Hak Cipta (english)',
					'name' => 'ugm_options_footer_copyright_en',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 4,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_580d7672c69cf',
			'title' => 'Pengaturan SEO',
			'fields' => array (
				array (
					'key' => 'field_580d7817992a1',
					'label' => 'Judul dan Deskripsi Kustom',
					'name' => 'ugm_options_enable_custom_title',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'enable' => 'Aktif',
						'disable' => 'Non-Aktif',
					),
					'default_value' => array (
						'disable' => 'disable',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_580d78d5992a2',
					'label' => 'Judul Situs Kustom',
					'name' => 'ugm_options_custom_site_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => 60,
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_580d7945992a3',
					'label' => 'Deskripsi Situs Kustom',
					'name' => 'ugm_options_custom_site_description',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => 160,
					'rows' => 3,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_580d7a9f992a4',
					'label' => 'Open Graph dan VCard',
					'name' => 'ugm_options_enable_og',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'enable' => 'Aktif',
						'disable' => 'Non-Aktif',
					),
					'default_value' => array (
						'disable' => 'disable',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 6,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_57ff180d806b3',
			'title' => 'Layout Halaman',
			'fields' => array (
				array (
					'key' => 'field_57ff1816ac697',
					'label' => 'Tata letak',
					'name' => 'ugm_options_theme_layout',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'right' => 'Sidebar Kanan',
						'left' => 'Sidebar Kiri',
						'none' => 'Full Width',
						'left-right' => '3 Kolom',
					),
					'default_value' => array (
						'right' => 'right',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 9,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_5809f2b4b63d8',
			'title' => 'Kustomisasi',
			'fields' => array (
				array (
					'key' => 'field_5809f2bf4a324',
					'label' => 'CSS',
					'name' => 'ugm_options_custom_css',
					'type' => 'acf_code_field',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'mode' => 'css',
					'theme' => 'monokai',
				),
				array (
					'key' => 'field_5809f4490f0de',
					'label' => 'JS',
					'name' => 'ugm_options_custom_js',
					'type' => 'acf_code_field',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'mode' => 'javascript',
					'theme' => 'monokai',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'theme-options',
					),
				),
			),
			'menu_order' => 10,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		acf_add_local_field_group(array (
			'key' => 'group_580d7c83f411a',
			'title' => 'SEO',
			'fields' => array (
				array (
					'key' => 'field_580d7c8b37cc7',
					'label' => 'Tag Judul',
					'name' => 'ugm_seo_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => 60,
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_580d7cda37cc8',
					'label' => 'Deskripsi Meta',
					'name' => 'ugm_seo_description',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => 160,
					'rows' => 3,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/contact-page.php',
					),
					array (
						'param' => 'page_template',
						'operator' => '!=',
						'value' => 'page-templates/profile-page.php',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 10,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	}

	public function acf_settings_path( $path ) {
		$path = UGM_THEME_DIR_INC . '/libs/advanced-custom-fields-pro/';
		return $path;
	}

	public function acf_settings_dir( $dir ) {
		$dir = UGM_THEME_URI_INC . '/libs/advanced-custom-fields-pro/';
		return $dir;
	}

}
new UGM_Custom_Fields();
