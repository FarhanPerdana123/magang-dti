<?php

if ( class_exists( 'QuadLayers\\WP_Plugin_Table_Links\\Load' ) ) {
	add_action('init', function() {
		new \QuadLayers\WP_Plugin_Table_Links\Load(
			QUADMENU_PLUGIN_FILE,
			array(
				array(
					'text'   => esc_html__( 'Settings', 'quadmenu' ),
					'url'    => admin_url( 'admin.php?page=' . QUADMENU_PANEL ),
					'target' => '_self',
				),
				array(
					'text' => esc_html__( 'Premium', 'quadmenu' ),
					'url'  => 'https://quadmenu.com/?utm_source=quadmenu_plugin&utm_medium=plugin_table&utm_campaign=premium_upgrade&utm_content=premium_link',
					'target' => '_blank',
					'color' => 'green',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Support', 'quadmenu' ),
					'url'   => 'https://quadmenu.com/account/support/?utm_source=quadmenu_plugin&utm_medium=plugin_table&utm_campaign=support&utm_content=support_link',
				),
				array(
					'place' => 'row_meta',
					'text'  => esc_html__( 'Documentation', 'quadmenu' ),
					'url'   => 'https://quadmenu.com/documentation/?utm_source=quadmenu_plugin&utm_medium=plugin_table&utm_campaign=documentation&utm_content=documentation_link',
				),
			)
		);
	});
}
