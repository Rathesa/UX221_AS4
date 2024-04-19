<?php
 function wp_get_layout_definitions() { $layout_definitions = array( 'default' => array( 'name' => 'default', 'slug' => 'flow', 'className' => 'is-layout-flow', 'baseStyles' => array( array( 'selector' => ' > .alignleft', 'rules' => array( 'float' => 'left', 'margin-inline-start' => '0', 'margin-inline-end' => '2em', ), ), array( 'selector' => ' > .alignright', 'rules' => array( 'float' => 'right', 'margin-inline-start' => '2em', 'margin-inline-end' => '0', ), ), array( 'selector' => ' > .aligncenter', 'rules' => array( 'margin-left' => 'auto !important', 'margin-right' => 'auto !important', ), ), ), 'spacingStyles' => array( array( 'selector' => ' > :first-child:first-child', 'rules' => array( 'margin-block-start' => '0', ), ), array( 'selector' => ' > :last-child:last-child', 'rules' => array( 'margin-block-end' => '0', ), ), array( 'selector' => ' > *', 'rules' => array( 'margin-block-start' => null, 'margin-block-end' => '0', ), ), ), ), 'constrained' => array( 'name' => 'constrained', 'slug' => 'constrained', 'className' => 'is-layout-constrained', 'baseStyles' => array( array( 'selector' => ' > .alignleft', 'rules' => array( 'float' => 'left', 'margin-inline-start' => '0', 'margin-inline-end' => '2em', ), ), array( 'selector' => ' > .alignright', 'rules' => array( 'float' => 'right', 'margin-inline-start' => '2em', 'margin-inline-end' => '0', ), ), array( 'selector' => ' > .aligncenter', 'rules' => array( 'margin-left' => 'auto !important', 'margin-right' => 'auto !important', ), ), array( 'selector' => ' > :where(:not(.alignleft):not(.alignright):not(.alignfull))', 'rules' => array( 'max-width' => 'var(--wp--style--global--content-size)', 'margin-left' => 'auto !important', 'margin-right' => 'auto !important', ), ), array( 'selector' => ' > .alignwide', 'rules' => array( 'max-width' => 'var(--wp--style--global--wide-size)', ), ), ), 'spacingStyles' => array( array( 'selector' => ' > :first-child:first-child', 'rules' => array( 'margin-block-start' => '0', ), ), array( 'selector' => ' > :last-child:last-child', 'rules' => array( 'margin-block-end' => '0', ), ), array( 'selector' => ' > *', 'rules' => array( 'margin-block-start' => null, 'margin-block-end' => '0', ), ), ), ), 'flex' => array( 'name' => 'flex', 'slug' => 'flex', 'className' => 'is-layout-flex', 'displayMode' => 'flex', 'baseStyles' => array( array( 'selector' => '', 'rules' => array( 'flex-wrap' => 'wrap', 'align-items' => 'center', ), ), array( 'selector' => ' > *', 'rules' => array( 'margin' => '0', ), ), ), 'spacingStyles' => array( array( 'selector' => '', 'rules' => array( 'gap' => null, ), ), ), ), 'grid' => array( 'name' => 'grid', 'slug' => 'grid', 'className' => 'is-layout-grid', 'displayMode' => 'grid', 'baseStyles' => array( array( 'selector' => ' > *', 'rules' => array( 'margin' => '0', ), ), ), 'spacingStyles' => array( array( 'selector' => '', 'rules' => array( 'gap' => null, ), ), ), ), ); return $layout_definitions; } function wp_register_layout_support( $block_type ) { $support_layout = block_has_support( $block_type, 'layout', false ) || block_has_support( $block_type, '__experimentalLayout', false ); if ( $support_layout ) { if ( ! $block_type->attributes ) { $block_type->attributes = array(); } if ( ! array_key_exists( 'layout', $block_type->attributes ) ) { $block_type->attributes['layout'] = array( 'type' => 'object', ); } } } function wp_get_layout_style( $selector, $layout, $has_block_gap_support = false, $gap_value = null, $should_skip_gap_serialization = false, $fallback_gap_value = '0.5em', $block_spacing = null ) { $layout_type = isset( $layout['type'] ) ? $layout['type'] : 'default'; $layout_styles = array(); if ( 'default' === $layout_type ) { if ( $has_block_gap_support ) { if ( is_array( $gap_value ) ) { $gap_value = isset( $gap_value['top'] ) ? $gap_value['top'] : null; } if ( null !== $gap_value && ! $should_skip_gap_serialization ) { if ( is_string( $gap_value ) && str_contains( $gap_value, 'var:preset|spacing|' ) ) { $index_to_splice = strrpos( $gap_value, '|' ) + 1; $slug = _wp_to_kebab_case( substr( $gap_value, $index_to_splice ) ); $gap_value = "var(--wp--preset--spacing--$slug)"; } array_push( $layout_styles, array( 'selector' => "$selector > *", 'declarations' => array( 'margin-block-start' => '0', 'margin-block-end' => '0', ), ), array( 'selector' => "$selector$selector > * + *", 'declarations' => array( 'margin-block-start' => $gap_value, 'margin-block-end' => '0', ), ) ); } } } elseif ( 'constrained' === $layout_type ) { $content_size = isset( $layout['contentSize'] ) ? $layout['contentSize'] : ''; $wide_size = isset( $layout['wideSize'] ) ? $layout['wideSize'] : ''; $justify_content = isset( $layout['justifyContent'] ) ? $layout['justifyContent'] : 'center'; $all_max_width_value = $content_size ? $content_size : $wide_size; $wide_max_width_value = $wide_size ? $wide_size : $content_size; $all_max_width_value = safecss_filter_attr( explode( ';', $all_max_width_value )[0] ); $wide_max_width_value = safecss_filter_attr( explode( ';', $wide_max_width_value )[0] ); $margin_left = 'left' === $justify_content ? '0 !important' : 'auto !important'; $margin_right = 'right' === $justify_content ? '0 !important' : 'auto !important'; if ( $content_size || $wide_size ) { array_push( $layout_styles, array( 'selector' => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))", 'declarations' => array( 'max-width' => $all_max_width_value, 'margin-left' => $margin_left, 'margin-right' => $margin_right, ), ), array( 'selector' => "$selector > .alignwide", 'declarations' => array( 'max-width' => $wide_max_width_value ), ), array( 'selector' => "$selector .alignfull", 'declarations' => array( 'max-width' => 'none' ), ) ); if ( isset( $block_spacing ) ) { $block_spacing_values = wp_style_engine_get_styles( array( 'spacing' => $block_spacing, ) ); if ( isset( $block_spacing_values['declarations']['padding-right'] ) ) { $padding_right = $block_spacing_values['declarations']['padding-right']; $layout_styles[] = array( 'selector' => "$selector > .alignfull", 'declarations' => array( 'margin-right' => "calc($padding_right * -1)" ), ); } if ( isset( $block_spacing_values['declarations']['padding-left'] ) ) { $padding_left = $block_spacing_values['declarations']['padding-left']; $layout_styles[] = array( 'selector' => "$selector > .alignfull", 'declarations' => array( 'margin-left' => "calc($padding_left * -1)" ), ); } } } if ( 'left' === $justify_content ) { $layout_styles[] = array( 'selector' => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))", 'declarations' => array( 'margin-left' => '0 !important' ), ); } if ( 'right' === $justify_content ) { $layout_styles[] = array( 'selector' => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))", 'declarations' => array( 'margin-right' => '0 !important' ), ); } if ( $has_block_gap_support ) { if ( is_array( $gap_value ) ) { $gap_value = isset( $gap_value['top'] ) ? $gap_value['top'] : null; } if ( null !== $gap_value && ! $should_skip_gap_serialization ) { if ( is_string( $gap_value ) && str_contains( $gap_value, 'var:preset|spacing|' ) ) { $index_to_splice = strrpos( $gap_value, '|' ) + 1; $slug = _wp_to_kebab_case( substr( $gap_value, $index_to_splice ) ); $gap_value = "var(--wp--preset--spacing--$slug)"; } array_push( $layout_styles, array( 'selector' => "$selector > *", 'declarations' => array( 'margin-block-start' => '0', 'margin-block-end' => '0', ), ), array( 'selector' => "$selector$selector > * + *", 'declarations' => array( 'margin-block-start' => $gap_value, 'margin-block-end' => '0', ), ) ); } } } elseif ( 'flex' === $layout_type ) { $layout_orientation = isset( $layout['orientation'] ) ? $layout['orientation'] : 'horizontal'; $justify_content_options = array( 'left' => 'flex-start', 'right' => 'flex-end', 'center' => 'center', ); $vertical_alignment_options = array( 'top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end', ); if ( 'horizontal' === $layout_orientation ) { $justify_content_options += array( 'space-between' => 'space-between' ); $vertical_alignment_options += array( 'stretch' => 'stretch' ); } else { $justify_content_options += array( 'stretch' => 'stretch' ); $vertical_alignment_options += array( 'space-between' => 'space-between' ); } if ( ! empty( $layout['flexWrap'] ) && 'nowrap' === $layout['flexWrap'] ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'flex-wrap' => 'nowrap' ), ); } if ( $has_block_gap_support && isset( $gap_value ) ) { $combined_gap_value = ''; $gap_sides = is_array( $gap_value ) ? array( 'top', 'left' ) : array( 'top' ); foreach ( $gap_sides as $gap_side ) { $process_value = $gap_value; if ( is_array( $gap_value ) ) { $process_value = isset( $gap_value[ $gap_side ] ) ? $gap_value[ $gap_side ] : $fallback_gap_value; } if ( is_string( $process_value ) && str_contains( $process_value, 'var:preset|spacing|' ) ) { $index_to_splice = strrpos( $process_value, '|' ) + 1; $slug = _wp_to_kebab_case( substr( $process_value, $index_to_splice ) ); $process_value = "var(--wp--preset--spacing--$slug)"; } $combined_gap_value .= "$process_value "; } $gap_value = trim( $combined_gap_value ); if ( null !== $gap_value && ! $should_skip_gap_serialization ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'gap' => $gap_value ), ); } } if ( 'horizontal' === $layout_orientation ) { if ( ! empty( $layout['justifyContent'] ) && array_key_exists( $layout['justifyContent'], $justify_content_options ) ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'justify-content' => $justify_content_options[ $layout['justifyContent'] ] ), ); } if ( ! empty( $layout['verticalAlignment'] ) && array_key_exists( $layout['verticalAlignment'], $vertical_alignment_options ) ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'align-items' => $vertical_alignment_options[ $layout['verticalAlignment'] ] ), ); } } else { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'flex-direction' => 'column' ), ); if ( ! empty( $layout['justifyContent'] ) && array_key_exists( $layout['justifyContent'], $justify_content_options ) ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'align-items' => $justify_content_options[ $layout['justifyContent'] ] ), ); } else { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'align-items' => 'flex-start' ), ); } if ( ! empty( $layout['verticalAlignment'] ) && array_key_exists( $layout['verticalAlignment'], $vertical_alignment_options ) ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'justify-content' => $vertical_alignment_options[ $layout['verticalAlignment'] ] ), ); } } } elseif ( 'grid' === $layout_type ) { if ( ! empty( $layout['columnCount'] ) ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'grid-template-columns' => 'repeat(' . $layout['columnCount'] . ', minmax(0, 1fr))' ), ); } else { $minimum_column_width = ! empty( $layout['minimumColumnWidth'] ) ? $layout['minimumColumnWidth'] : '12rem'; $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'grid-template-columns' => 'repeat(auto-fill, minmax(min(' . $minimum_column_width . ', 100%), 1fr))' ), ); } if ( $has_block_gap_support && isset( $gap_value ) ) { $combined_gap_value = ''; $gap_sides = is_array( $gap_value ) ? array( 'top', 'left' ) : array( 'top' ); foreach ( $gap_sides as $gap_side ) { $process_value = $gap_value; if ( is_array( $gap_value ) ) { $process_value = isset( $gap_value[ $gap_side ] ) ? $gap_value[ $gap_side ] : $fallback_gap_value; } if ( is_string( $process_value ) && str_contains( $process_value, 'var:preset|spacing|' ) ) { $index_to_splice = strrpos( $process_value, '|' ) + 1; $slug = _wp_to_kebab_case( substr( $process_value, $index_to_splice ) ); $process_value = "var(--wp--preset--spacing--$slug)"; } $combined_gap_value .= "$process_value "; } $gap_value = trim( $combined_gap_value ); if ( null !== $gap_value && ! $should_skip_gap_serialization ) { $layout_styles[] = array( 'selector' => $selector, 'declarations' => array( 'gap' => $gap_value ), ); } } } if ( ! empty( $layout_styles ) ) { return wp_style_engine_get_stylesheet_from_css_rules( $layout_styles, array( 'context' => 'block-supports', 'prettify' => false, ) ); } return ''; } function wp_render_layout_support_flag( $block_content, $block ) { $block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] ); $block_supports_layout = block_has_support( $block_type, 'layout', false ) || block_has_support( $block_type, '__experimentalLayout', false ); $layout_from_parent = isset( $block['attrs']['style']['layout']['selfStretch'] ) ? $block['attrs']['style']['layout']['selfStretch'] : null; if ( ! $block_supports_layout && ! $layout_from_parent ) { return $block_content; } $outer_class_names = array(); if ( 'fixed' === $layout_from_parent || 'fill' === $layout_from_parent ) { $container_content_class = wp_unique_id( 'wp-container-content-' ); $child_layout_styles = array(); if ( 'fixed' === $layout_from_parent && isset( $block['attrs']['style']['layout']['flexSize'] ) ) { $child_layout_styles[] = array( 'selector' => ".$container_content_class", 'declarations' => array( 'flex-basis' => $block['attrs']['style']['layout']['flexSize'], 'box-sizing' => 'border-box', ), ); } elseif ( 'fill' === $layout_from_parent ) { $child_layout_styles[] = array( 'selector' => ".$container_content_class", 'declarations' => array( 'flex-grow' => '1', ), ); } wp_style_engine_get_stylesheet_from_css_rules( $child_layout_styles, array( 'context' => 'block-supports', 'prettify' => false, ) ); $outer_class_names[] = $container_content_class; } $processor = new WP_HTML_Tag_Processor( $block_content ); if ( ! $processor->next_tag() ) { return $block_content; } if ( ! $block_supports_layout && ! empty( $outer_class_names ) ) { foreach ( $outer_class_names as $class_name ) { $processor->add_class( $class_name ); } return $processor->get_updated_html(); } elseif ( ! $block_supports_layout ) { return $block_content; } $global_settings = wp_get_global_settings(); $fallback_layout = isset( $block_type->supports['layout']['default'] ) ? $block_type->supports['layout']['default'] : array(); if ( empty( $fallback_layout ) ) { $fallback_layout = isset( $block_type->supports['__experimentalLayout']['default'] ) ? $block_type->supports['__experimentalLayout']['default'] : array(); } $used_layout = isset( $block['attrs']['layout'] ) ? $block['attrs']['layout'] : $fallback_layout; $class_names = array(); $layout_definitions = wp_get_layout_definitions(); $container_class = wp_unique_prefixed_id( 'wp-container-' . sanitize_title( $block['blockName'] ) . '-is-layout-' ); if ( isset( $used_layout['inherit'] ) && $used_layout['inherit'] || isset( $used_layout['contentSize'] ) && $used_layout['contentSize'] ) { $used_layout['type'] = 'constrained'; } $root_padding_aware_alignments = isset( $global_settings['useRootPaddingAwareAlignments'] ) ? $global_settings['useRootPaddingAwareAlignments'] : false; if ( $root_padding_aware_alignments && isset( $used_layout['type'] ) && 'constrained' === $used_layout['type'] ) { $class_names[] = 'has-global-padding'; } if ( ! empty( $block['attrs']['layout']['orientation'] ) ) { $class_names[] = 'is-' . sanitize_title( $block['attrs']['layout']['orientation'] ); } if ( ! empty( $block['attrs']['layout']['justifyContent'] ) ) { $class_names[] = 'is-content-justification-' . sanitize_title( $block['attrs']['layout']['justifyContent'] ); } if ( ! empty( $block['attrs']['layout']['flexWrap'] ) && 'nowrap' === $block['attrs']['layout']['flexWrap'] ) { $class_names[] = 'is-nowrap'; } if ( isset( $used_layout['type'] ) ) { $layout_classname = isset( $layout_definitions[ $used_layout['type'] ]['className'] ) ? $layout_definitions[ $used_layout['type'] ]['className'] : ''; } else { $layout_classname = isset( $layout_definitions['default']['className'] ) ? $layout_definitions['default']['className'] : ''; } if ( $layout_classname && is_string( $layout_classname ) ) { $class_names[] = sanitize_title( $layout_classname ); } if ( ! current_theme_supports( 'disable-layout-styles' ) ) { $gap_value = isset( $block['attrs']['style']['spacing']['blockGap'] ) ? $block['attrs']['style']['spacing']['blockGap'] : null; if ( is_array( $gap_value ) ) { foreach ( $gap_value as $key => $value ) { $gap_value[ $key ] = $value && preg_match( '%[\\\(&=}]|/\*%', $value ) ? null : $value; } } else { $gap_value = $gap_value && preg_match( '%[\\\(&=}]|/\*%', $gap_value ) ? null : $gap_value; } $fallback_gap_value = isset( $block_type->supports['spacing']['blockGap']['__experimentalDefault'] ) ? $block_type->supports['spacing']['blockGap']['__experimentalDefault'] : '0.5em'; $block_spacing = isset( $block['attrs']['style']['spacing'] ) ? $block['attrs']['style']['spacing'] : null; $should_skip_gap_serialization = wp_should_skip_block_supports_serialization( $block_type, 'spacing', 'blockGap' ); $block_gap = isset( $global_settings['spacing']['blockGap'] ) ? $global_settings['spacing']['blockGap'] : null; $has_block_gap_support = isset( $block_gap ); $style = wp_get_layout_style( ".$container_class.$container_class", $used_layout, $has_block_gap_support, $gap_value, $should_skip_gap_serialization, $fallback_gap_value, $block_spacing ); if ( ! empty( $style ) ) { $class_names[] = $container_class; } } $block_name = explode( '/', $block['blockName'] ); $class_names[] = 'wp-block-' . end( $block_name ) . '-' . $layout_classname; if ( ! empty( $outer_class_names ) ) { foreach ( $outer_class_names as $outer_class_name ) { $processor->add_class( $outer_class_name ); } } $inner_block_wrapper_classes = null; $first_chunk = isset( $block['innerContent'][0] ) ? $block['innerContent'][0] : null; if ( is_string( $first_chunk ) && count( $block['innerContent'] ) > 1 ) { $first_chunk_processor = new WP_HTML_Tag_Processor( $first_chunk ); while ( $first_chunk_processor->next_tag() ) { $class_attribute = $first_chunk_processor->get_attribute( 'class' ); if ( is_string( $class_attribute ) && ! empty( $class_attribute ) ) { $inner_block_wrapper_classes = $class_attribute; } } } do { if ( ! $inner_block_wrapper_classes ) { break; } $class_attribute = $processor->get_attribute( 'class' ); if ( is_string( $class_attribute ) && str_contains( $class_attribute, $inner_block_wrapper_classes ) ) { break; } } while ( $processor->next_tag() ); foreach ( $class_names as $class_name ) { $processor->add_class( $class_name ); } return $processor->get_updated_html(); } WP_Block_Supports::get_instance()->register( 'layout', array( 'register_attribute' => 'wp_register_layout_support', ) ); add_filter( 'render_block', 'wp_render_layout_support_flag', 10, 2 ); function wp_restore_group_inner_container( $block_content, $block ) { $tag_name = isset( $block['attrs']['tagName'] ) ? $block['attrs']['tagName'] : 'div'; $group_with_inner_container_regex = sprintf( '/(^\s*<%1$s\b[^>]*wp-block-group(\s|")[^>]*>)(\s*<div\b[^>]*wp-block-group__inner-container(\s|")[^>]*>)((.|\S|\s)*)/U', preg_quote( $tag_name, '/' ) ); if ( wp_theme_has_theme_json() || 1 === preg_match( $group_with_inner_container_regex, $block_content ) || ( isset( $block['attrs']['layout']['type'] ) && 'flex' === $block['attrs']['layout']['type'] ) ) { return $block_content; } $layout_classes = array(); $processor = new WP_HTML_Tag_Processor( $block_content ); if ( $processor->next_tag( array( 'class_name' => 'wp-block-group' ) ) ) { foreach ( $processor->class_list() as $class_name ) { if ( str_contains( $class_name, 'is-layout-' ) ) { $layout_classes[] = $class_name; $processor->remove_class( $class_name ); } } } $content_without_layout_classes = $processor->get_updated_html(); $replace_regex = sprintf( '/(^\s*<%1$s\b[^>]*wp-block-group[^>]*>)(.*)(<\/%1$s>\s*$)/ms', preg_quote( $tag_name, '/' ) ); $updated_content = preg_replace_callback( $replace_regex, static function ( $matches ) { return $matches[1] . '<div class="wp-block-group__inner-container">' . $matches[2] . '</div>' . $matches[3]; }, $content_without_layout_classes ); if ( ! empty( $layout_classes ) ) { $processor = new WP_HTML_Tag_Processor( $updated_content ); if ( $processor->next_tag( array( 'class_name' => 'wp-block-group__inner-container' ) ) ) { foreach ( $layout_classes as $class_name ) { $processor->add_class( $class_name ); } } $updated_content = $processor->get_updated_html(); } return $updated_content; } add_filter( 'render_block_core/group', 'wp_restore_group_inner_container', 10, 2 ); function wp_restore_image_outer_container( $block_content, $block ) { $image_with_align = "
/# 1) everything up to the class attribute contents
(
	^\s*
	<figure\b
	[^>]*
	\bclass=
	[\"']
)
# 2) the class attribute contents
(
	[^\"']*
	\bwp-block-image\b
	[^\"']*
	\b(?:alignleft|alignright|aligncenter)\b
	[^\"']*
)
# 3) everything after the class attribute contents
(
	[\"']
	[^>]*
	>
	.*
	<\/figure>
)/iUx"; if ( wp_theme_has_theme_json() || 0 === preg_match( $image_with_align, $block_content, $matches ) ) { return $block_content; } $wrapper_classnames = array( 'wp-block-image' ); if ( ! empty( $block['attrs']['className'] ) ) { $wrapper_classnames = array_merge( $wrapper_classnames, explode( ' ', $block['attrs']['className'] ) ); } $content_classnames = explode( ' ', $matches[2] ); $filtered_content_classnames = array_diff( $content_classnames, $wrapper_classnames ); return '<div class="' . implode( ' ', $wrapper_classnames ) . '">' . $matches[1] . implode( ' ', $filtered_content_classnames ) . $matches[3] . '</div>'; } add_filter( 'render_block_core/image', 'wp_restore_image_outer_container', 10, 2 ); 