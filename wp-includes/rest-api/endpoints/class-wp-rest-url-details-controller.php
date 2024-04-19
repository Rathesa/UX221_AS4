<?php
 class WP_REST_URL_Details_Controller extends WP_REST_Controller { public function __construct() { $this->namespace = 'wp-block-editor/v1'; $this->rest_base = 'url-details'; } public function register_routes() { register_rest_route( $this->namespace, '/' . $this->rest_base, array( array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'parse_url_details' ), 'args' => array( 'url' => array( 'required' => true, 'description' => __( 'The URL to process.' ), 'validate_callback' => 'wp_http_validate_url', 'sanitize_callback' => 'sanitize_url', 'type' => 'string', 'format' => 'uri', ), ), 'permission_callback' => array( $this, 'permissions_check' ), 'schema' => array( $this, 'get_public_item_schema' ), ), ) ); } public function get_item_schema() { if ( $this->schema ) { return $this->add_additional_fields_schema( $this->schema ); } $this->schema = array( '$schema' => 'http://json-schema.org/draft-04/schema#', 'title' => 'url-details', 'type' => 'object', 'properties' => array( 'title' => array( 'description' => sprintf( __( 'The contents of the %s element from the URL.' ), '<title>' ), 'type' => 'string', 'context' => array( 'view', 'edit', 'embed' ), 'readonly' => true, ), 'icon' => array( 'description' => sprintf( __( 'The favicon image link of the %s element from the URL.' ), '<link rel="icon">' ), 'type' => 'string', 'format' => 'uri', 'context' => array( 'view', 'edit', 'embed' ), 'readonly' => true, ), 'description' => array( 'description' => sprintf( __( 'The content of the %s element from the URL.' ), '<meta name="description">' ), 'type' => 'string', 'context' => array( 'view', 'edit', 'embed' ), 'readonly' => true, ), 'image' => array( 'description' => sprintf( __( 'The Open Graph image link of the %1$s or %2$s element from the URL.' ), '<meta property="og:image">', '<meta property="og:image:url">' ), 'type' => 'string', 'format' => 'uri', 'context' => array( 'view', 'edit', 'embed' ), 'readonly' => true, ), ), ); return $this->add_additional_fields_schema( $this->schema ); } public function parse_url_details( $request ) { $url = untrailingslashit( $request['url'] ); if ( empty( $url ) ) { return new WP_Error( 'rest_invalid_url', __( 'Invalid URL' ), array( 'status' => 404 ) ); } $cache_key = $this->build_cache_key_for_url( $url ); $cached_response = $this->get_cache( $cache_key ); if ( ! empty( $cached_response ) ) { $remote_url_response = $cached_response; } else { $remote_url_response = $this->get_remote_url( $url ); if ( is_wp_error( $remote_url_response ) || empty( $remote_url_response ) ) { return $remote_url_response; } $this->set_cache( $cache_key, $remote_url_response ); } $html_head = $this->get_document_head( $remote_url_response ); $meta_elements = $this->get_meta_with_content_elements( $html_head ); $data = $this->add_additional_fields_to_object( array( 'title' => $this->get_title( $html_head ), 'icon' => $this->get_icon( $html_head, $url ), 'description' => $this->get_description( $meta_elements ), 'image' => $this->get_image( $meta_elements, $url ), ), $request ); $response = rest_ensure_response( $data ); return apply_filters( 'rest_prepare_url_details', $response, $url, $request, $remote_url_response ); } public function permissions_check() { if ( current_user_can( 'edit_posts' ) ) { return true; } foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) { if ( current_user_can( $post_type->cap->edit_posts ) ) { return true; } } return new WP_Error( 'rest_cannot_view_url_details', __( 'Sorry, you are not allowed to process remote URLs.' ), array( 'status' => rest_authorization_required_code() ) ); } private function get_remote_url( $url ) { $modified_user_agent = 'WP-URLDetails/' . get_bloginfo( 'version' ) . ' (+' . get_bloginfo( 'url' ) . ')'; $args = array( 'limit_response_size' => 150 * KB_IN_BYTES, 'user-agent' => $modified_user_agent, ); $args = apply_filters( 'rest_url_details_http_request_args', $args, $url ); $response = wp_safe_remote_get( $url, $args ); if ( WP_Http::OK !== wp_remote_retrieve_response_code( $response ) ) { return new WP_Error( 'no_response', __( 'URL not found. Response returned a non-200 status code for this URL.' ), array( 'status' => WP_Http::NOT_FOUND ) ); } $remote_body = wp_remote_retrieve_body( $response ); if ( empty( $remote_body ) ) { return new WP_Error( 'no_content', __( 'Unable to retrieve body from response at this URL.' ), array( 'status' => WP_Http::NOT_FOUND ) ); } return $remote_body; } private function get_title( $html ) { $pattern = '#<title[^>]*>(.*?)<\s*/\s*title>#is'; preg_match( $pattern, $html, $match_title ); if ( empty( $match_title[1] ) || ! is_string( $match_title[1] ) ) { return ''; } $title = trim( $match_title[1] ); return $this->prepare_metadata_for_output( $title ); } private function get_icon( $html, $url ) { $pattern = '#<link\s[^>]*rel=(?:[\"\']??)\s*(?:icon|shortcut icon|icon shortcut)\s*(?:[\"\']??)[^>]*\/?>#isU'; preg_match( $pattern, $html, $element ); if ( empty( $element[0] ) || ! is_string( $element[0] ) ) { return ''; } $element = trim( $element[0] ); $pattern = '#href=([\"\']??)([^\" >]*?)\\1[^>]*#isU'; preg_match( $pattern, $element, $icon ); if ( empty( $icon[2] ) || ! is_string( $icon[2] ) ) { return ''; } $icon = trim( $icon[2] ); $parsed_icon = parse_url( $icon ); if ( isset( $parsed_icon['scheme'] ) && 'data' === $parsed_icon['scheme'] ) { return $icon; } if ( ! is_string( $url ) || '' === $url ) { return $icon; } $parsed_url = parse_url( $url ); if ( isset( $parsed_url['scheme'] ) && isset( $parsed_url['host'] ) ) { $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/'; $icon = WP_Http::make_absolute_url( $icon, $root_url ); } return $icon; } private function get_description( $meta_elements ) { if ( empty( $meta_elements[0] ) ) { return ''; } $description = $this->get_metadata_from_meta_element( $meta_elements, 'name', '(?:description|og:description)' ); if ( '' === $description ) { return ''; } return $this->prepare_metadata_for_output( $description ); } private function get_image( $meta_elements, $url ) { $image = $this->get_metadata_from_meta_element( $meta_elements, 'property', '(?:og:image|og:image:url)' ); if ( '' === $image ) { return ''; } $parsed_url = parse_url( $url ); if ( isset( $parsed_url['scheme'] ) && isset( $parsed_url['host'] ) ) { $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/'; $image = WP_Http::make_absolute_url( $image, $root_url ); } return $image; } private function prepare_metadata_for_output( $metadata ) { $metadata = html_entity_decode( $metadata, ENT_QUOTES, get_bloginfo( 'charset' ) ); $metadata = wp_strip_all_tags( $metadata ); return $metadata; } private function build_cache_key_for_url( $url ) { return 'g_url_details_response_' . md5( $url ); } private function get_cache( $key ) { return get_site_transient( $key ); } private function set_cache( $key, $data = '' ) { $ttl = HOUR_IN_SECONDS; $cache_expiration = apply_filters( 'rest_url_details_cache_expiration', $ttl ); return set_site_transient( $key, $data, $cache_expiration ); } private function get_document_head( $html ) { $head_html = $html; $head_start = strpos( $html, '<head' ); if ( false === $head_start ) { return $html; } $head_end = strpos( $head_html, '</head>' ); if ( false === $head_end ) { $head_end = strpos( $head_html, '<body' ); if ( false === $head_end ) { return $html; } } $head_html = substr( $head_html, $head_start, $head_end ); $head_html .= '</head>'; return $head_html; } private function get_meta_with_content_elements( $html ) { $pattern = '#<meta\s' . '[^>]*' . 'content=(["\']??)(.*)\1' . '[^>]*' . '\/?>#' . 'isU'; preg_match_all( $pattern, $html, $elements ); return $elements; } private function get_metadata_from_meta_element( $meta_elements, $attr, $attr_value ) { if ( empty( $meta_elements[0] ) ) { return ''; } $metadata = ''; $pattern = '#' . $attr . '=([\"\']??)\s*' . $attr_value . '\s*\1' . '#isU'; foreach ( $meta_elements[0] as $index => $element ) { preg_match( $pattern, $element, $match ); if ( empty( $match ) ) { continue; } if ( isset( $meta_elements[2][ $index ] ) && is_string( $meta_elements[2][ $index ] ) ) { $metadata = trim( $meta_elements[2][ $index ] ); } break; } return $metadata; } } 