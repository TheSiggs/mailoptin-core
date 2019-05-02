<?php

namespace MailOptin\Core\OptinForms;

use MailOptin\Core\Admin\Customizer\OptinForm\OptinFormFactory;
use MailOptin\Core\Repositories\OptinCampaignsRepository as Repository;
use MailOptin\Core\Repositories\OptinCampaignsRepository;

class FrontEndOutput
{
    use PageTargetingRuleTrait, UserTargetingRuleTrait;

    public function __construct()
    {
        add_action('wp_footer', array($this, 'load_optin'), 999999999);
    }

    public function load_optin()
    {
        if (is_customize_preview() || is_admin()) return;

        if (isset($_GET['mohide']) && $_GET['mohide'] == 'true') return;

        if (apply_filters('mo_disable_frontend_optin_output', false)) return;

        $optin_ids = get_transient('mo_get_optin_ids_footer_display');

        if ($optin_ids === false) {
            $optin_ids = Repository::get_optin_campaign_ids(['sidebar', 'inpost']);
            set_transient('mo_get_optin_ids_footer_display', $optin_ids, HOUR_IN_SECONDS);
        }

        foreach ($optin_ids as $id) {

            $id = absint($id);

            do_action('mailoptin_before_footer_optin_display', $id, $optin_ids);

            // if it is a split test variant, skip
            if (Repository::is_split_test_variant($id)) continue;

            // if optin is not enabled, pass. for split test, this ensure parent is active before choosing split test
            // variant to display
            if ( ! Repository::is_activated($id)) continue;

            $id = Repository::choose_split_test_variant($id);

            if ( ! OptinCampaignsRepository::is_test_mode($id)) {

                // if optin global exit/interaction and success cookie result fails, move to next.
                if ( ! Repository::global_cookie_check_result()) continue;

                if ( ! apply_filters('mailoptin_show_optin_form', true, $id)) continue;

                if ( ! $this->user_targeting_rule_checker($id)) {
                    continue;
                }

                if ( ! $this->page_level_targeting_rule_checker($id)) {
                    continue;
                }

                if ( ! $this->query_level_targeting_rule_checker($id)) {
                    continue;
                }
      
            }

            echo OptinFormFactory::build($id);

            do_action('mailoptin_after_footer_optin_display', $id, $optin_ids);
        }
    }

    /**
     * @return FrontEndOutput
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Query level output checker
     */
    public function query_level_targeting_rule_checker( $id )
    {
        if (! defined('MAILOPTIN_DETACH_LIBSODIUM') ) {
            return true;
        }

        $action = sanitize_text_field( Repository::get_customizer_value($id, 'filter_query_action') );
        $query  = sanitize_text_field( Repository::get_customizer_value($id, 'filter_query_string') );
        $value  = sanitize_text_field( Repository::get_customizer_value($id, 'filter_query_value') );
        $match  = false;
        
        if( ! $action || $action == '0' ){
            return true;
        }

        if( $action && $query && isset($_GET[$query]) && ( empty($value) || $_GET[$query] == $value )){
            $match  = true;
        }

        if( 'hide' == $action ){
            return !$match;
        }

        return $match;

    }
}