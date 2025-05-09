<?php
/*
 * Plugin Name: Блокировка доменов, имён и IP с синхронизацией через GitHub
 * Description: Запрещает регистрацию с определённых доменов, имён и IP, с анализом частоты регистраций и синхронизацией через GitHub
 * Version: 2.3.31
 * Author: Grok (xAI)
 * Update URI: https://plugindom.ru/api/plugin-update
 */

// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

// Подключаем файлы
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocking-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/github-sync.php';
require_once plugin_dir_path(__FILE__) . 'includes/plugin-update.php';

/* Подключаем стили и скрипты */
function bed_admin_enqueue() {
    wp_enqueue_style('bed-admin-style', plugins_url('admin-style.css', __FILE__), [], '2.3.31');
    wp_enqueue_script('bed-admin-script', plugins_url('admin-script.js', __FILE__), ['jquery'], '2.3.31', true);
    wp_localize_script('bed-admin-script', 'bedAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bed_ajax_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'bed_admin_enqueue');

/* Добавляем страницу в меню "Настройки" */
function bed_add_admin_menu() {
    add_options_page(
        'Блокировка доменов, имён и IP',
        'Блокировка доменов, имён и IP',
        'manage_options',
        'bed_settings_page',
        'bed_settings_page_callback'
    );
}
add_action('admin_menu', 'bed_add_admin_menu');

/* Добавляем ссылку "Настройки" под плагином */
function bed_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=bed_settings_page') . '">' . __('Настройки', 'textdomain') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bed_add_settings_link');

/* Регистрируем настройки */
function bed_register_settings() {
    register_setting('bed_settings_group', 'bed_blocked_domains', 'sanitize_textarea_field');
    register_setting('bed_settings_group', 'bed_blocked_usernames', 'sanitize_textarea_field');
    register_setting('bed_settings_group', 'bed_blocked_ips', 'sanitize_textarea_field');
    register_setting('bed_settings_group', 'bed_blocked_stats', 'sanitize_text_field');
    register_setting('bed_settings_group', 'bed_successful_registrations', 'sanitize_text_field');
    register_setting('bed_settings_group', 'bed_suspicious_activity', 'sanitize_text_field');
    register_setting('bed_settings_group', 'bed_frequency_settings', 'bed_sanitize_frequency_settings');
    register_setting('bed_settings_group', 'bed_github_token', 'sanitize_text_field');
    register_setting('bed_settings_group', 'bed_github_repo', 'sanitize_text_field');
}
add_action('admin_init', 'bed_register_settings');
?>
