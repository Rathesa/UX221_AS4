<?php
 function wp_interactivity_process_directives_of_interactive_blocks( array $parsed_block ): array { static $root_interactive_block = null; if ( null === $root_interactive_block ) { $block_name = $parsed_block['blockName']; $block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name ); if ( isset( $block_name ) && ( ( isset( $block_type->supports['interactivity'] ) && true === $block_type->supports['interactivity'] ) || ( isset( $block_type->supports['interactivity']['interactive'] ) && true === $block_type->supports['interactivity']['interactive'] ) ) ) { $root_interactive_block = array( $block_name, $parsed_block ); $process_interactive_blocks = static function ( string $content, array $parsed_block ) use ( &$root_interactive_block, &$process_interactive_blocks ): string { list($root_block_name, $root_parsed_block) = $root_interactive_block; if ( $root_block_name === $parsed_block['blockName'] && $parsed_block === $root_parsed_block ) { $content = wp_interactivity_process_directives( $content ); remove_filter( 'render_block_' . $parsed_block['blockName'], $process_interactive_blocks ); $root_interactive_block = null; } return $content; }; add_filter( 'render_block_' . $block_name, $process_interactive_blocks, 100, 2 ); } } return $parsed_block; } add_filter( 'render_block_data', 'wp_interactivity_process_directives_of_interactive_blocks', 100, 1 ); function wp_interactivity(): WP_Interactivity_API { global $wp_interactivity; if ( ! ( $wp_interactivity instanceof WP_Interactivity_API ) ) { $wp_interactivity = new WP_Interactivity_API(); } return $wp_interactivity; } function wp_interactivity_process_directives( string $html ): string { return wp_interactivity()->process_directives( $html ); } function wp_interactivity_state( string $store_namespace, array $state = array() ): array { return wp_interactivity()->state( $store_namespace, $state ); } function wp_interactivity_config( string $store_namespace, array $config = array() ): array { return wp_interactivity()->config( $store_namespace, $config ); } function wp_interactivity_data_wp_context( array $context, string $store_namespace = '' ): string { return 'data-wp-context=\'' . ( $store_namespace ? $store_namespace . '::' : '' ) . ( empty( $context ) ? '{}' : wp_json_encode( $context, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ) . '\''; } 