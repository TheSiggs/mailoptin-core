<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
use W3Guy\Custom_Settings_Page_Api;

if (!defined('ABSPATH')) {
    exit;
}


class Connections extends AbstractSettingsPage
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_settings_page'));

        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('removable_query_args', array($this, 'removable_query_args'));

        add_action('wp_cspa_after_persist_settings', [$this, 'bust_all_connection_cache'], 10, 2);
    }

    /**
     * Delete or burst all connection cache when connection settings is (re-) saved.
     *
     * @param array $sanitized_data
     * @param string $option_name
     */
    public function bust_all_connection_cache($sanitized_data, $option_name)
    {
        if ($option_name === MAILOPTIN_CONNECTIONS_DB_OPTION_NAME) {
            global $wpdb;
            $table = $wpdb->prefix . 'options';

            $wpdb->query("DELETE FROM $table where option_name LIKE '%_mo_connection_cache_%'");
        }
    }

    public function register_settings_page()
    {
        add_submenu_page(
            'mailoptin-settings',
            __('Connections - MailOptin', 'mailoptin'),
            __('Connections', 'mailoptin'),
            'manage_options',
            'mailoptin-connections',
            array($this, 'settings_admin_page_callback')
        );
    }

    public function settings_admin_page_callback()
    {
        do_action('mailoptin_before_connections_settings_page', MAILOPTIN_CONNECTIONS_DB_OPTION_NAME);

        $args = apply_filters('mailoptin_connections_settings_page', array());

        $instance = Custom_Settings_Page_Api::instance($args, MAILOPTIN_CONNECTIONS_DB_OPTION_NAME, __('Connections', 'mailoptin'));
        $this->register_core_settings($instance);
        $instance->build();

        do_action('mailoptin_after_connections_settings_page', MAILOPTIN_CONNECTIONS_DB_OPTION_NAME);

        // close all connection settings page
        echo '<script type="text/javascript">';
        echo "jQuery(window).load(function() {jQuery('#post-body-content').find('button.handlediv.button-link').click();});";
        echo '</script>';
    }

    public function admin_notices()
    {
        // handle oauth errors.
        if (isset($_GET['mo-oauth-provider'], $_GET['mo-oauth-error'])) {
            $provider = ucfirst(sanitize_text_field($_GET['mo-oauth-provider']));
            $error_message = strtolower(sanitize_text_field($_GET['mo-oauth-error']));

            echo '<div id="message" class="updated notice is-dismissible">';
            echo '<p>';
            echo "$provider $error_message";
            echo '</p>';
            echo '</div>';
        }
    }

    public function removable_query_args($args)
    {
        $args[] = 'mo-oauth-provider';
        $args[] = 'mo-oauth-error';
        $args[] = 'code';

        return $args;
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}