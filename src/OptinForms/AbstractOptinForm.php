<?php

namespace MailOptin\Core\OptinForms;

use MailOptin\Core\Admin\Customizer\OptinForm\AbstractCustomizer;
use MailOptin\Core\Admin\Customizer\OptinForm\Customizer;
use MailOptin\Core\Admin\Customizer\OptinForm\CustomizerSettings;
use MailOptin\Core\PluginSettings\Settings;
use MailOptin\Core\RegisterScripts;
use MailOptin\Core\Repositories\OptinCampaignsRepository;

abstract class AbstractOptinForm extends AbstractCustomizer implements OptinFormInterface
{
    /** @var int optin campaign ID */
    protected $optin_campaign_id;

    /** @var string optin universal unique ID */
    protected $optin_campaign_uuid;

    /** @var string optin campaign type */
    protected $optin_campaign_type;

    /** @var string optin campaign class */
    protected $optin_campaign_class;

    /** @var string optin wrapper CSS ID */
    protected $optin_css_id;

    // feature support flags
    public $cta_button = 'cta_button';

    /**
     * ID of optin form.
     *
     * @param int $optin_campaign_id
     * @param \WP_Customize_Manager $wp_customize
     */
    public function __construct($optin_campaign_id = 0, $wp_customize = null)
    {
        // isn't included in if condition below because it is reused by front end optin output.
        $this->optin_campaign_id = $optin_campaign_id;
        $this->optin_campaign_uuid = OptinCampaignsRepository::get_optin_campaign_uuid($optin_campaign_id);
        $this->optin_campaign_type = OptinCampaignsRepository::get_optin_campaign_type($optin_campaign_id);
        $this->optin_campaign_class = OptinCampaignsRepository::get_optin_campaign_class($optin_campaign_id);
        $this->optin_css_id = "{$this->optin_campaign_uuid}_{$this->optin_campaign_type}";

        if (!empty($_REQUEST['mailoptin_optin_campaign_id'])) {
            add_filter('mo_optin_form_customizer_design_settings', [$this, 'customizer_design_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_headline_settings', [$this, 'customizer_headline_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_description_settings', [$this, 'customizer_description_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_note_settings', [$this, 'customizer_note_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_fields_settings', [$this, 'customizer_fields_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_configuration_settings', [$this, 'customizer_configuration_settings'], 10, 2);
            add_filter('mo_optin_form_customizer_output_settings', [$this, 'customizer_output_settings'], 10, 2);

            add_filter('mo_optin_form_customizer_design_controls', [$this, 'customizer_design_controls'], 10, 4);
            add_filter('mo_optin_form_customizer_headline_controls', [$this, 'customizer_headline_controls'], 10, 4);
            add_filter('mo_optin_form_customizer_description_controls', [$this, 'customizer_description_controls'], 10, 4);
            add_filter('mo_optin_form_customizer_note_controls', array($this, 'customizer_note_controls'), 10, 4);
            add_filter('mo_optin_form_customizer_fields_controls', [$this, 'customizer_fields_controls'], 10, 4);
            add_filter('mo_optin_form_customizer_configuration_controls', [$this, 'customizer_configuration_controls'], 10, 4);
            add_filter('mo_optin_form_customizer_output_controls', [$this, 'customizer_output_controls'], 10, 4);

            add_action('customize_preview_init', array($this, 'optin_form_customizer_javascript'));

            add_action('customize_preview_init', array(RegisterScripts::get_instance(), 'modal_scripts'));
        }

        parent::__construct($optin_campaign_id);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function features_support()
    {
        return [];
    }

    /**
     * Ensure all optin class specific filters are run in the context of their instance and type.
     *
     * @param mixed $configs
     */
    public function init_config_filters($configs)
    {
        foreach ($configs as $config) {
            $filter_name = $config['name'];
            $optin_class = isset($config['optin_class']) ? $config['optin_class'] : '';
            $optin_type = isset($config['optin_type']) ? $config['optin_type'] : '';
            $filter_value = $config['value'];
            $priority = isset($config['priority']) ? $config['priority'] : 10;
            $accepted_arg = isset($config['accepted_arg']) ? $config['accepted_arg'] : 1;

            if (is_callable($filter_value)) {
                add_filter($filter_name, $filter_value, $priority, $accepted_arg);
            } else {
                add_filter($filter_name, function ($value, $customizer_defaults, $optin_campaign_type, $optin_campaign_class) use ($optin_class, $optin_type, $filter_value) {
                    if ($optin_campaign_class == $optin_class && $optin_campaign_type == $optin_type) {
                        $value = $filter_value;
                    }
                    return $value;
                }, 10, 4);
            }
        }
    }

    /**
     * Font with space are suppose to be enclose in double quote else, unquoted.
     *
     * @param $val
     * @param string $fallback fallback font
     *
     * @return mixed|string
     */
    public function _construct_font_family($val, $fallback = 'Helvetica, Arial, sans-serif;')
    {
        $font_family = self::_replace_plus_with_space($val);

        return strpos($font_family, ' ') ? "'$font_family', $fallback" : $font_family . ", $fallback";
    }

    /**
     * Replace + with space.
     *
     * @param string $val
     *
     * @return mixed
     */
    public static function _replace_plus_with_space($val)
    {
        return str_replace('+', ' ', $val);
    }

    /**
     * Replace space with +.
     *
     * @param string $val
     *
     * @return mixed
     */
    public function _replace_space_with_plus($val)
    {
        return str_replace(' ', '+', $val);
    }

    /**
     * If font is a web safe font, default to empty.
     *
     * @param string $font
     *
     * @return string
     */
    public static function _remove_web_safe_font($font)
    {
        $web_safe_font = [
            'Helvetica',
            'Helvetica Neue',
            'Arial',
            'Times New Roman',
            'Lucida Sans',
            'Verdana',
            'Tahoma',
            'Cambria',
            'Trebuchet MS',
            'Segoe UI'
        ];

        $font = self::_replace_plus_with_space($font);

        return in_array($font, $web_safe_font) ? '' : $font;
    }

    /**
     * Enqueue optin form customizer JavaScript.
     *
     * @return mixed
     */
    public function optin_form_customizer_javascript()
    {
        $optin_form_name = OptinCampaignsRepository::get_optin_campaign_name($this->optin_campaign_id);
        $optin_form_name = preg_replace('/\s+/', '-', $optin_form_name);

        wp_enqueue_script(
            "mailoptin-optin-form-customizer-{$optin_form_name}",
            MAILOPTIN_ASSETS_URL . 'js/admin/optin-customizer.js',
            array('customize-preview', 'jquery'),
            false,
            true
        );

        do_action('mailoptin_optin_customizer_javascript_enqueue', MAILOPTIN_ASSETS_URL, $optin_form_name);

        $optin_campaign_option_prefix = MO_OPTIN_CAMPAIGN_WP_OPTION_NAME;
        wp_add_inline_script(
            "customize-preview",
            "var mailoptin_optin_campaign_id = $this->optin_campaign_id;
            var mailoptin_optin_option_prefix  = '$optin_campaign_option_prefix'"
        );
    }

    /**
     * Return value of a optin form customizer settings.
     *
     * @param string $optin_form_setting
     *
     * @return string
     */
    public function get_customizer_value($optin_form_setting)
    {
        $default = isset($this->customizer_defaults[$optin_form_setting]) ? $this->customizer_defaults[$optin_form_setting] : '';

        return OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, $optin_form_setting, $default);
    }

    /**
     * Track optin impression.
     *
     * @return string
     */
    public function impression_tracker_js_script()
    {
        return "<script type='text/javascript'>jQuery(document.body).on('mo-mailoptinjs-loaded', function(){
if (typeof jQuery.MailOptin !== 'undefined' && typeof jQuery.MailOptin.track_impression !== 'undefined') {
    jQuery.MailOptin.track_impression('{$this->optin_campaign_uuid}');
  }
});</script>";
    }

    /**
     * Load Google fonts.
     *
     * @return string
     */
    public function webfont_loader_js_script()
    {
        $optin_form_fonts = $this->get_optin_form_fonts();

        if (!empty($optin_form_fonts)) {
            return "<script type='text/javascript'>jQuery(function(){if(typeof WebFont!=='undefined'){WebFont.load({google: {families: [$optin_form_fonts]}});}});</script>";
        }
    }

    /**
     * Full HTML doctype markup preview of a optin form.
     *
     * @return string
     */
    public function get_preview_structure()
    {
        $optin_form_fonts = $this->get_optin_form_fonts();

        // set body padding-top to 0 if optin is bar.
        $body_padding_top = in_array($this->optin_campaign_type, ['bar']) ? 0 : '10%';
        ob_start();

        wp_head();

        echo "<body style='background: #f3f3f3 !important;padding-top:$body_padding_top;'>";

        if (in_array($this->optin_campaign_type,
            ['sidebar', 'inpost'])) { // ensure sidebar and inpost optin has a max width for preview sake.
            echo '<div style="max-width:700px;margin: auto">';
        }

        echo $this->get_optin_form_structure();

        if (in_array($this->optin_campaign_type, ['sidebar', 'inpost'])) {
            echo '</div>';
        }

        if (is_customize_preview()) {
            echo '<style id="mo-customizer-preview-custom-css"></style>';
            // hide any element that might have been injected to footer by any plugin.
            echo '<div style="display:none">';
            wp_footer();
            echo '</div>';
            // script below is below wp_footer() because jquery must have been loaded by now.
            if (!empty($optin_form_fonts)) {
                echo "<script type='text/javascript'>jQuery(function(){if(typeof WebFont!=='undefined'){WebFont.load({google: {families: [$optin_form_fonts]}})}});</script>";
            }
        }
        echo '</body>';

        return ob_get_clean();
    }


    /**
     * Global optin CSS.
     *
     * @param string $optin_css_id
     * @param string $optin_campaign_uuid
     *
     * @return string
     */
    public function global_css($optin_css_id, $optin_campaign_uuid = '')
    {
        $global_css = "div#{$optin_campaign_uuid} *, div#{$optin_campaign_uuid} *:before, div#{$optin_campaign_uuid} *:after {box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;}";
        $global_css .= "div#{$optin_css_id}_container div.mo-optin-powered-by{margin:5px auto 2px;text-align:center;}";
        $global_css .= "div#{$optin_css_id}_container div.mo-optin-powered-by a {font-size:16px !important; text-decoration: none !important;box-shadow:none !important;border-bottom-width:0px !important;cursor:pointer !important;}";

        if ($this->optin_campaign_type == 'bar') {
            $global_css .= "div#$optin_campaign_uuid.mo-optin-form-bar-top {top: 0;position: absolute;}";
            $global_css .= "div#$optin_campaign_uuid.mo-optin-form-bar-bottom {bottom: 0;position: fixed;}";
            $global_css .= "div#$optin_campaign_uuid.mo-optin-form-bar-sticky {position: fixed;}";
            $global_css .= '.admin-bar .mo-optin-form-bar-top {top: 32px !important;}';
            $global_css .= '@media screen and (max-width: 782px) { .admin-bar .mo-optin-form-bar-top { top: 46px !important; } }';
            $global_css .= '@media screen and (max-width: 600px) { .admin-bar .mo-optin-form-bar-top.mo-optin-form-bar-sticky { top: 0 !important; } }';
        }

        if ($this->optin_campaign_type == 'slidein') {
            $global_css .= "div#$optin_campaign_uuid.mo-slidein-bottom_right {right: 10px;}";
            $global_css .= "div#$optin_campaign_uuid.mo-slidein-bottom_left {left: 10px;}";
        }

        if ($this->get_customizer_value('hide_name_field')) {
            $global_css .= "div#$optin_css_id #mo-optin-form-name-field {display: none !important;}";
        }

        return apply_filters('mo_optin_form_global_css', $global_css, $optin_campaign_uuid, $optin_css_id);
    }

    /**
     * Return scripts and styles needed or that belongs to an optin form.
     *
     * @return string
     */
    public function optin_script_and_styles()
    {
        $custom_css = $this->get_customizer_value('form_custom_css');
        $script = '';
        $script .= "<script type=\"text/javascript\">{$this->optin_js_config()}</script>";

        $styles = '<style id="mo-optin-form-stylesheet" type="text/css">';
        $styles .= minify_css($this->optin_form_css() . $this->global_css($this->optin_css_id, $this->optin_campaign_uuid));
        $styles .= '</style>';
        $styles = apply_filters('mo_optin_form_css', $styles, $this->optin_campaign_uuid, $this->optin_campaign_id);

        if (!empty($custom_css)) {
            $custom_styles = '<style id="mo-optin-form-custom-css" type="text/css">';
            $custom_styles .= minify_css($custom_css);
            $custom_styles .= '</style>';
            $custom_styles = apply_filters('mo_optin_form_custom_css', $custom_styles, $this->optin_campaign_uuid, $this->optin_campaign_id);
        }

        return $script . $styles . $custom_styles . $this->optin_script();
    }

    public function is_schedule_display_rule_active()
    {
        $schedule_status = $this->get_customizer_value('schedule_status');
        $schedule_start = $this->get_customizer_value('schedule_start');
        $schedule_end = $this->get_customizer_value('schedule_end');
        $schedule_timezone = $this->get_customizer_value('schedule_timezone');

        return (!is_customize_preview() && !empty($schedule_status) && !empty($schedule_start) && !empty($schedule_end) && !empty($schedule_timezone));
    }

    /**
     * HTML and CSS structure of an optin form.
     */
    protected function _get_optin_form_structure()
    {
        $optin_css_id = $this->optin_css_id;
        $optin_campaign_uuid = $this->optin_campaign_uuid;
        $optin_form = '';
        $name_email_class_indicator = $this->get_customizer_value('hide_name_field') === true ? 'mo-has-email' : 'mo-has-name-email';
        $display_only_button_class_indicator = $this->get_customizer_value('display_only_button') === true ? ' mo-cta-button-display' : '';
        // set optin to display:none when schedule is active then allow mailoptinjs to decide whether to show it or not.
        $is_hidden_style = $this->is_schedule_display_rule_active() ? 'display: none' : '';

        if ($this->optin_campaign_type == 'lightbox') {
            $modalWrapperStyle = implode(';', [
                'display: none',
                'position: fixed',
                'zoom: 1',
                'text-align: center',
                'z-index: 99999999',
                'left: 0',
                'top: 0',
                'width: 100%',
                'height: 100%',
                'overflow: auto', // fixes overflow scrolling bar being shown :D
                'background: rgba(0,0,0,0.7)'
            ]);

            $optin_form .= "<div id='$optin_campaign_uuid' class=\"moOptinForm mo-optin-form-{$this->optin_campaign_type} {$name_email_class_indicator}{$display_only_button_class_indicator}\" data-optin-type='{$this->optin_campaign_type}' style='$modalWrapperStyle'>";
        }

        if ($this->optin_campaign_type == 'bar') {
            $position = $this->get_customizer_value('bar_position');
            $position_class = ' mo-optin-form-bar-' . $position;
            $is_sticky = $position == 'top' && $this->get_customizer_value('bar_sticky') ? ' mo-optin-form-bar-sticky' : '';

            $bar_wrapper_style_properties = [
                'display: none',
                'left: 0',
                'right: 0',
                'width: 100%',
                'margin: 0',
                'background: 0 0',
                'z-index: 99999'
            ];

            $barWrapperStyle = implode(';', $bar_wrapper_style_properties);
            $optin_form .= "<div id='$optin_campaign_uuid' class=\"moOptinForm mo-optin-form-{$this->optin_campaign_type} {$name_email_class_indicator}{$display_only_button_class_indicator}{$position_class}{$is_sticky}\" data-optin-type='{$this->optin_campaign_type}' style='$barWrapperStyle'>";
        }

        if ($this->optin_campaign_type == 'slidein') {
            $position = $this->get_customizer_value('slidein_position');
            $position = empty($position) ? 'bottom_right' : $position;
            $position_class = ' mo-slidein-' . $position;
            $slidein_wrapper_style_properties = [
                'display: none',
                'position: fixed',
                'bottom: 10px',
                'width: auto',
                'margin: 0',
                'background: 0 0',
                'z-index: 99999'
            ];
            $slideinWrapperStyle = implode(';', $slidein_wrapper_style_properties);
            $optin_form .= "<div id='$optin_campaign_uuid' class=\"moOptinForm mo-optin-form-{$this->optin_campaign_type}{$display_only_button_class_indicator}{$position_class}\" data-optin-type='{$this->optin_campaign_type}' style='$slideinWrapperStyle'>";
        }

        if ($this->optin_campaign_type == 'sidebar') {
            $sidebar_wrapper_style_properties = [$is_hidden_style];
            $sidebarWrapperStyle = implode(';', $sidebar_wrapper_style_properties);
            $optin_form .= "<div id='$optin_campaign_uuid' class=\"moOptinForm mo-optin-form-{$this->optin_campaign_type} {$name_email_class_indicator}{$display_only_button_class_indicator}\" data-optin-type='{$this->optin_campaign_type}' style='$sidebarWrapperStyle'>";
        }

        if ($this->optin_campaign_type == 'inpost') {
            $inpost_wrapper_style_properties = [$is_hidden_style];
            $inpostWrapperStyle = implode(';', $inpost_wrapper_style_properties);
            $optin_form .= "<div id='$optin_campaign_uuid' class=\"moOptinForm mo-optin-form-{$this->optin_campaign_type} {$name_email_class_indicator}{$display_only_button_class_indicator}\" data-optin-type='{$this->optin_campaign_type}' style='$inpostWrapperStyle'>";
        }

        $optin_form .= "<div class='mo-optin-form-container' id='{$optin_css_id}_container' style='position:relative;margin: 0 auto;'>";
        $optin_form .= $this->optin_script_and_styles();
        $optin_form .= apply_filters('mo_optin_form_shortcode_structure', do_shortcode($this->optin_form()), $this->optin_campaign_id, $this->optin_campaign_uuid, $this->optin_form());

        $optin_form .= "</div>";
        $optin_form .= "</div>";

        $output = PHP_EOL . apply_filters('mo_optin_form_attribution_start', '<!-- This site converts visitors into subscribers and customers with the MailOptin WordPress plugin v' . MAILOPTIN_VERSION_NUMBER . ' - https://mailoptin.io -->' . PHP_EOL);
        $output .= minify_html($optin_form);
        $output .= "<!-- / MailOptin WordPress plugin. -->" . PHP_EOL;

        return $output;
    }

    /**
     * Value of state after conversion customizer setting.
     *
     * @return string
     */
    public function state_after_conversion()
    {
        return $this->get_customizer_value('state_after_conversion');
    }

    /**
     * Cache proxy to retrieve the optin form structure.
     *
     * @return string
     */
    public function get_optin_form_structure()
    {
        /* $this->timestamp_spam_combat() in this section ensure the timestamp is never cached. */

        if (is_customize_preview()) return $this->_get_optin_form_structure();

        // Bypass cache if this optin form has successfully received opt-in from visitor and 'state after conversion' is not set to still display optin form.
        // so success message overlay will be shown instead of opt-in form.
        if (OptinCampaignsRepository::user_has_successful_optin($this->optin_campaign_uuid)) {

            // if state after conversion is set to 'optin form hidden', return nothing.
            if ($this->state_after_conversion() == 'optin_form_hidden') return '';

            return $this->_get_optin_form_structure() . $this->timestamp_spam_combat();
        }

        // if cache is disable, fetch fresh optin structure.
        if (apply_filters('mailoptin_disable_optin_form_cache', false)) return $this->_get_optin_form_structure() . $this->timestamp_spam_combat();

        $cache_key = "mo_get_optin_form_structure_{$this->optin_campaign_id}";
        $optin_structure = get_transient($cache_key);

        if (empty($optin_structure) || false === $optin_structure) {

            $optin_structure = $this->_get_optin_form_structure();
            set_transient(
                $cache_key,
                $optin_structure,
                apply_filters('mailoptin_get_optin_form_structure_cache_expiration', HOUR_IN_SECONDS)
            );
        }

        return $optin_structure . $this->timestamp_spam_combat();
    }

    /**
     * Generate honeypot field for optin form.
     *
     * @return string
     */
    public function timestamp_spam_combat()
    {
        // Give ability to turn off honeypot.
        if (apply_filters('mailoptin_disable_timestamp_spam_combat', false)) return '';

        return '<input id="' . $this->optin_css_id . '_honeypot_timestamp" type="hidden" name="mo-timestamp" value="' . time() . '" style="display:none" />';

    }

    /**
     * Optin form (Google) fonts.
     *
     * @return string
     */
    public function get_optin_form_fonts()
    {
        // retrieve uncached result if we are in customizer screen.
        if (apply_filters('mailoptin_disable_optin_form_cache', is_customize_preview())) {
            return $this->_get_optin_form_fonts();
        }

        $cache_key = "mo_get_optin_form_fonts_{$this->optin_campaign_id}";
        $optin_structure = get_transient($cache_key);

        if (empty($optin_structure) || false === $optin_structure) {

            $optin_structure = $this->_get_optin_form_fonts();

            set_transient(
                $cache_key,
                $optin_structure,
                apply_filters('mailoptin_get_optin_form_fonts_cache_expiration', HOUR_IN_SECONDS)
            );
        }

        return $optin_structure;
    }

    /**
     * Cache proxy to retrieve comma delimited list of optin form (Google) fonts.
     *
     * @return string
     */
    protected function _get_optin_form_fonts()
    {
        $default_headline_font = $this->customizer_defaults['headline_font'];
        $default_description_font = $this->customizer_defaults['description_font'];
        $default_note_font = $this->customizer_defaults['note_font'];
        $default_submit_button_font = $this->customizer_defaults['submit_button_font'];
        $default_cta_button_font = $this->customizer_defaults['cta_button_font'];

        $headline_font = apply_filters('mo_get_optin_form_headline_font',
            self::_remove_web_safe_font(
                OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'headline_font', $default_headline_font)
            ),
            'headline_font',
            $default_headline_font,
            $this->optin_campaign_id
        );

        $description_font = apply_filters('mo_get_optin_form_description_font',
            self::_remove_web_safe_font(
                OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'description_font', $default_description_font)
            ),
            'description_font',
            $default_description_font,
            $this->optin_campaign_id
        );

        $note_font = apply_filters('mo_get_optin_form_note_font',
            self::_remove_web_safe_font(
                OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'note_font', $default_note_font)
            ),
            'note_font',
            $default_note_font,
            $this->optin_campaign_id
        );

        $submit_button_font = apply_filters('mo_get_optin_form_submit_button_font',
            self::_remove_web_safe_font(
                OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'submit_button_font', $default_submit_button_font)
            ),
            'submit_button_font',
            $default_submit_button_font,
            $this->optin_campaign_id
        );

        $cta_button_font = apply_filters('mo_get_optin_form_cta_button_font',
            self::_remove_web_safe_font(
                OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'cta_button_font', $default_cta_button_font)
            ),
            'cta_button_font',
            $default_cta_button_font,
            $this->optin_campaign_id
        );

        // build the comma delimited webfonts
        $webfont = [];
        if (!empty($headline_font)) {
            $webfont[] = "'$headline_font'";
        }
        if (!empty($description_font)) {
            $webfont [] = "'$description_font'";
        }
        if (!empty($note_font)) {
            $webfont[] = "'$note_font'";
        }
        if (!empty($submit_button_font)) {
            $webfont[] = "'$submit_button_font'";
        }
        if (!empty($cta_button_font)) {
            $webfont[] = "'$cta_button_font'";
        }

        $webfont = apply_filters('mo_optin_form_fonts_list', $webfont, $this->optin_campaign_id);

        $delimiter = !empty($webfont) ? ',' : null;

        return implode(',', array_unique($webfont)) . $delimiter;
    }

    /**
     * Optin JS configuration.
     */
    public function optin_js_config()
    {
        $optin_campaign_id = $this->optin_campaign_id;

        $exit_cookie = $this->get_customizer_value('cookie');
        $success_cookie = $this->get_customizer_value('success_cookie');

        $global_exit_cookie = Settings::instance()->global_cookie();
        $global_success_cookie = Settings::instance()->global_success_cookie();

        $schedule_status = $this->get_customizer_value('schedule_status');
        $schedule_start = $this->get_customizer_value('schedule_start');
        $schedule_end = $this->get_customizer_value('schedule_end');
        $schedule_timezone = $this->get_customizer_value('schedule_timezone');

        $click_launch_status = $this->get_customizer_value('click_launch_status');
        $x_page_views_status = $this->get_customizer_value('x_page_views_status');
        $x_page_views_condition = $this->get_customizer_value('x_page_views_condition');
        $x_page_views_value = $this->get_customizer_value('x_page_views_value');

        $x_seconds_status = $this->get_customizer_value('x_seconds_status');
        $x_seconds_value = $this->get_customizer_value('x_seconds_value');

        $x_scroll_status = $this->get_customizer_value('x_scroll_status');
        $x_scroll_value = $this->get_customizer_value('x_scroll_value');

        $exit_intent_status = $this->get_customizer_value('exit_intent_status');

        $cta_display_only_button_status = $this->get_customizer_value('display_only_button');
        $cta_action_after_click = $this->get_customizer_value('cta_button_action');

        $data = array();
        $data['optin_uuid'] = $this->optin_campaign_uuid;
        $data['optin_campaign_id'] = $optin_campaign_id;
        $data['optin_type'] = OptinCampaignsRepository::get_optin_campaign_type($optin_campaign_id);
        $data['post_id'] = $post_id = is_singular() || is_front_page() ? get_queried_object_id() : 0;
        // must be of integer type for js-cookie to work.
        // am not using empty() because if cookie is set to 0, it returns true i.e 0 is empty.
        $data['cookie'] = $exit_cookie != '' ? absint($exit_cookie) : 30;
        // defaults to value of exit cookie above
        $data['success_cookie'] = $success_cookie != '' ? absint($success_cookie) : $data['cookie'];
        $data['global_cookie'] = $global_exit_cookie != '' ? absint($global_exit_cookie) : 0;
        $data['global_success_cookie'] = $global_success_cookie != '' ? absint($global_success_cookie) : 0;
        $data['success_message'] = $this->get_customizer_value('success_message');

        /** click launch display rule */
        if ($click_launch_status === true) {
            $data['click_launch_status'] = $click_launch_status;
        }

        /** x page view display rule */
        if ($x_page_views_status === true && $x_page_views_condition != '...' && !empty($x_page_views_value)) {
            $data['x_page_views_status'] = $x_page_views_status;
            $data['x_page_views_condition'] = $x_page_views_condition;
            $data['x_page_views_value'] = absint($x_page_views_value);
        }

        /** after x seconds display rule */
        if ($x_seconds_status === true && !empty($x_seconds_value)) {
            $data['x_seconds_status'] = $x_seconds_status;
            $data['x_seconds_value'] = absint($x_seconds_value);
        }

        /** after x scroll percentage display rule */
        if ($x_scroll_status === true && !empty($x_scroll_value)) {
            $data['x_scroll_status'] = $x_scroll_status;
            $data['x_scroll_value'] = absint($x_scroll_value);
        }

        /** exit intent display rule */
        if ($exit_intent_status === true && !empty($exit_intent_status)) {
            $data['exit_intent_status'] = $exit_intent_status;
        }

        /** after conversion / success actions */
        $success_action = $this->get_customizer_value('success_action');
        $data['success_action'] = $success_action;

        if ($success_action == 'redirect_url') {
            $data['redirect_url_value'] = esc_url($this->get_customizer_value('redirect_url_value'));
            $data['pass_lead_data'] = $this->get_customizer_value('pass_lead_data_redirect_url');
        }

        $success_js_script = $this->get_customizer_value('success_js_script');
        if (!empty($success_js_script)) {
            $data['success_js_script'] = $success_js_script;
        }

        $data['test_mode'] = OptinCampaignsRepository::is_test_mode($optin_campaign_id);
        $icon_close_config = $this->optin_campaign_type == 'lightbox' ? apply_filters('mo_optin_campaign_icon_close', true, $this->optin_campaign_class, $this->optin_campaign_type) : false;

        // if close button is set to be hidden, return false for $icon_close_config.
        if ($icon_close_config) {
            if ($this->get_customizer_value('hide_close_button')) {
                $icon_close_config = false;
            }
        }

        $data['icon_close'] = $icon_close_config;

        if ($this->optin_campaign_type == 'lightbox') {
            $data['body_close'] = apply_filters('mo_optin_campaign_body_close', true);
            $data['keyClose'] = apply_filters('mo_optin_campaign_key_close', true);
        }

        if ($this->optin_campaign_type == 'bar') {
            $data['bar_position'] = $this->get_customizer_value('bar_position');
        }

        if ($this->optin_campaign_type == 'slidein') {
            $data['slidein_position'] = $this->get_customizer_value('slidein_position');
        }

        if ($schedule_status === true && !empty($schedule_start) && !empty($schedule_end) && !empty($schedule_timezone)) {
            $data['schedule_status'] = $schedule_status;
            $data['schedule_start'] = $schedule_start;
            $data['schedule_end'] = $schedule_end;
            $data['schedule_timezone'] = $schedule_timezone;
        }

        $data['unexpected_error'] = apply_filters('mo_optin_campaign_unexpected_error', __('Unexpected error. Please try again.', 'mailoptin'));
        $data['email_missing_error'] = apply_filters('mo_optin_campaign_email_missing_error', __('Please enter a valid email.', 'mailoptin'));
        $data['name_missing_error'] = apply_filters('mo_optin_campaign_name_missing_error', __('Please enter a valid name.', 'mailoptin'));
        $data['honeypot_error'] = apply_filters('mo_optin_campaign_honeypot_error', __('Your submission has been flagged as potential spam.', 'mailoptin'));

        /** CTA button: navigation url support */
        if ($cta_display_only_button_status) {
            $data['cta_display'] = true;
            $data['cta_action'] = $cta_action_after_click;
            if ($cta_action_after_click == 'navigate_to_url') {
                $data['cta_navigate_url'] = $this->get_customizer_value('cta_button_navigation_url');
            }
        }

        $data = apply_filters('mo_optin_js_config', $data, $this);

        $json = json_encode($data);

        $script = "var $this->optin_campaign_uuid = {$this->optin_campaign_uuid}_{$this->optin_campaign_type} = $json;";

        return $script;
    }

    /**
     * Customizer settings for optin form design.
     *
     * Any optin theme that wish to modify any of these settings should implement the method(s)
     *
     * @param mixed $settings
     *
     * @return mixed
     */
    abstract public function customizer_design_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form headline.
     *
     * Any optin theme that wish to modify any of these settings should implement the method(s)
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_headline_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form description.
     *
     * Any optin theme that wish to modify any of these settings should implement the method(s)
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_description_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form note.
     *
     * Any optin theme that wish to modify any of these settings should implement the method(s)
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_note_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form fields.
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_fields_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form configuration.
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_configuration_settings($settings, $CustomizerSettingsInstance);

    /**
     * Customizer settings for optin form output.
     *
     * @param mixed $settings
     *
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    abstract public function customizer_output_settings($settings, $CustomizerSettingsInstance);


    // --------------------------------------------- Optin form customizer controls. --------------------------------------- //

    /**
     * Customizer controls for optin form design settings.
     *
     * Any optin theme that wish to modify any of these controls should implement the method(s)
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_design_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form headline settings.
     *
     * Any optin theme that wish to modify any of these controls should implement the method(s)
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_headline_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form description settings.
     *
     * Any optin theme that wish to modify any of these controls should implement the method(s)
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_description_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form note settings.
     *
     * Any optin theme that wish to modify any of these controls should implement the method(s)
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_note_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form fields settings.
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_fields_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form configuration settings.
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_configuration_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );

    /**
     * Customizer controls for optin form output settings.
     *
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     *
     * @return mixed
     */
    abstract public function customizer_output_controls(
        $controls,
        $wp_customize,
        $option_prefix,
        $customizerClassInstance
    );
}