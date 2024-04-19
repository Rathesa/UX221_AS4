<?php
 ignore_user_abort( true ); if ( ! headers_sent() ) { header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' ); header( 'Cache-Control: no-cache, must-revalidate, max-age=0' ); } if ( PHP_VERSION_ID >= 70016 && function_exists( 'fastcgi_finish_request' ) ) { fastcgi_finish_request(); } elseif ( function_exists( 'litespeed_finish_request' ) ) { litespeed_finish_request(); } if ( ! empty( $_POST ) || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) { die(); } define( 'DOING_CRON', true ); if ( ! defined( 'ABSPATH' ) ) { require_once __DIR__ . '/wp-load.php'; } wp_raise_memory_limit( 'cron' ); function _get_cron_lock() { global $wpdb; $value = 0; if ( wp_using_ext_object_cache() ) { $value = wp_cache_get( 'doing_cron', 'transient', true ); } else { $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", '_transient_doing_cron' ) ); if ( is_object( $row ) ) { $value = $row->option_value; } } return $value; } $crons = wp_get_ready_cron_jobs(); if ( empty( $crons ) ) { die(); } $gmt_time = microtime( true ); $doing_cron_transient = get_transient( 'doing_cron' ); if ( empty( $doing_wp_cron ) ) { if ( empty( $_GET['doing_wp_cron'] ) ) { if ( $doing_cron_transient && ( $doing_cron_transient + WP_CRON_LOCK_TIMEOUT > $gmt_time ) ) { return; } $doing_wp_cron = sprintf( '%.22F', microtime( true ) ); $doing_cron_transient = $doing_wp_cron; set_transient( 'doing_cron', $doing_wp_cron ); } else { $doing_wp_cron = $_GET['doing_wp_cron']; } } if ( $doing_cron_transient !== $doing_wp_cron ) { return; } foreach ( $crons as $timestamp => $cronhooks ) { if ( $timestamp > $gmt_time ) { break; } foreach ( $cronhooks as $hook => $keys ) { foreach ( $keys as $k => $v ) { $schedule = $v['schedule']; if ( $schedule ) { $result = wp_reschedule_event( $timestamp, $schedule, $hook, $v['args'], true ); if ( is_wp_error( $result ) ) { error_log( sprintf( __( 'Cron reschedule event error for hook: %1$s, Error code: %2$s, Error message: %3$s, Data: %4$s' ), $hook, $result->get_error_code(), $result->get_error_message(), wp_json_encode( $v ) ) ); do_action( 'cron_reschedule_event_error', $result, $hook, $v ); } } $result = wp_unschedule_event( $timestamp, $hook, $v['args'], true ); if ( is_wp_error( $result ) ) { error_log( sprintf( __( 'Cron unschedule event error for hook: %1$s, Error code: %2$s, Error message: %3$s, Data: %4$s' ), $hook, $result->get_error_code(), $result->get_error_message(), wp_json_encode( $v ) ) ); do_action( 'cron_unschedule_event_error', $result, $hook, $v ); } do_action_ref_array( $hook, $v['args'] ); if ( _get_cron_lock() !== $doing_wp_cron ) { return; } } } } if ( _get_cron_lock() === $doing_wp_cron ) { delete_transient( 'doing_cron' ); } die(); 