<?php

namespace MailOptin\Core\OptinForms\Lightbox;

use MailOptin\Core\Admin\Customizer\OptinForm\CustomizerSettings;
use MailOptin\Core\OptinForms\AbstractOptinTheme;

class BareMetal extends AbstractOptinTheme
{
    public $optin_form_name = 'Bare Metal';

    public function __construct($optin_campaign_id, $wp_customize = '')
    {
        // -- default for design sections -- //
        add_filter('mo_optin_form_background_color_default', function () {
            return '#f0f0f0';
        });

        add_filter('mo_optin_form_border_color_default', function () {
            return '#dd3333';
        });

        // -- default for headline sections -- //
        add_filter('mo_optin_form_headline_default', function () {
            return __("Get a little acid in your inbox", 'mailoptin');
        });

        add_filter('mo_optin_form_headline_font_color_default', function () {
            return '#222222';
        });

        add_filter('mo_optin_form_headline_font_default', function () {
            return 'Arimo';
        });

        // -- default for description sections -- //
        add_filter('mo_optin_form_description_font_default', function () {
            return 'Muli';
        });

        add_filter('mo_optin_form_description_default', function () {
            return $this->_description_content();
        });

        add_filter('mo_optin_form_description_font_color_default', function () {
            return '#000000';
        });

        // -- default for fields sections -- //
        add_filter('mo_optin_form_name_field_color_default', function () {
            return '#222222';
        });

        add_filter('mo_optin_form_email_field_color_default', function () {
            return '#222222';
        });

        add_filter('mo_optin_form_submit_button_color_default', function () {
            return '#ffffff';
        });

        add_filter('mo_optin_form_submit_button_background_default', function () {
            return '#0073b7';
        });

        add_filter('mo_optin_form_submit_button_font_default', function () {
            return 'Helvetica+Neue';
        });

        add_filter('mo_optin_form_name_field_font_default', function () {
            return 'Consolas, Lucida Console, monospace';
        });

        add_filter('mo_optin_form_email_field_font_default', function () {
            return 'Consolas, Lucida Console, monospace';
        });

        // -- default for note sections -- //
        add_filter('mo_optin_form_note_font_color_default', function () {
            return '#000000';
        });

        add_filter('mo_optin_form_note_default', function () {
            return '<em>' . __('We promise not to spam you. You can unsubscribe at any time', 'mailoptin') . '</em>';
        });

        add_filter('mo_optin_form_note_font_default', function () {
            return 'Source+Sans+Pro';
        });

        add_filter('mailoptin_customizer_optin_campaign_MailChimpConnect_segment_display_style', function () {
            return 'inline';
        });

        add_filter('mailoptin_customizer_optin_campaign_MailChimpConnect_segment_display_alignment', function () {
            return 'center';
        });

        add_filter('mailoptin_customizer_optin_campaign_MailChimpConnect_user_input_field_color', function () {
            return '#000000';
        });

        parent::__construct($optin_campaign_id);
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_design_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_design_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_headline_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_headline_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_description_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_description_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_note_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_note_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }


    /**
     * @param mixed $fields_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_fields_settings($fields_settings, $CustomizerSettingsInstance)
    {
        return $fields_settings;
    }

    /**
     * @param array $fields_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_fields_controls($fields_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $fields_controls;
    }

    /**
     * @param mixed $configuration_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_configuration_settings($configuration_settings, $CustomizerSettingsInstance)
    {
        return $configuration_settings;
    }


    /**
     * @param array $configuration_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_configuration_controls($configuration_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $configuration_controls;
    }

    /**
     * @param mixed $output_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_output_settings($output_settings, $CustomizerSettingsInstance)
    {
        return $output_settings;
    }


    /**
     * @param array $output_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_output_controls($output_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $output_controls;
    }

    /**
     * Default description content.
     *
     * @return string
     */
    private function _description_content()
    {
        return '<p style="text-align: center;">Get special offers that you won\'t ever find on our blog·</p>';
    }

    /**
     * Fulfil interface contract.
     */
    public function optin_script()
    {
    }

    /**
     * Template body.
     *
     * @return string
     */
    public function optin_form()
    {
        return <<<HTML
[mo-optin-form-wrapper class="mo-baremetal-container"]
    [mo-optin-form-headline]
    [mo-optin-form-description class="mo-baremetal-description"]
    [mo-optin-form-error]
    [mo-optin-form-fields-wrapper]
    [mo-optin-form-name-field]
    [mo-optin-form-email-field]
    [/mo-optin-form-fields-wrapper]
    [mo-optin-form-submit-button]
    [mo-mailchimp-interests]
    [mo-optin-form-note class="mo-baremetal-note"]
[/mo-optin-form-wrapper]
HTML;
    }


    /**
     * Template CSS styling.
     *
     * @return string
     */
    public function optin_form_css()
    {
        $optin_css_id = $this->optin_css_id;

        return <<<CSS
div#$optin_css_id.mo-baremetal-container {
text-align:left;
background: #f0f0f0;
border: 4px solid #dd3333;
border-radius: 5px;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
-webkit-border-radius: 5px;
-o-border-radius: 5px;
-moz-border-radius: 5px;
padding: 1.5em;
margin: 10px;
max-width: 650px;
}

div#$optin_css_id.mo-baremetal-container h2.mo-optin-form-headline {
color: #222222;
font-family: "Arimo", sans-serif;
margin: 0 0 10px;
font-size: 24px;
font-weight: bold;
text-align: center;
}

div#$optin_css_id.mo-baremetal-container .mo-baremetal-description {
color: #000000;
font-family: "Source Sans Pro", sans-serif;
font-weight: normal;
font-size: 16px;
line-height: 1.6;
margin-bottom: 20px;
text-rendering: optimizeLegibility;
}

div#$optin_css_id .mo-baremetal-note {
  color:#000000;
  margin: 5px auto;
  text-align: center;
  font-style: italic;
  font-size: 16px;
  font-family: "Source Sans Pro", sans-serif;
}

div#$optin_css_id.mo-baremetal-container #{$optin_css_id}_name_field,
div#$optin_css_id.mo-baremetal-container #{$optin_css_id}_email_field {
-webkit-appearance: none;
-webkit-border-radius: 0;
border-radius: 0;
background: #fff;
border: 1px solid #ccc;
-webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
margin: 0;
padding: 8px;
height: 37px;
width: 100%;
max-width: initial;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
-webkit-transition: -webkit-box-shadow 0.45s, border-color 0.45s ease-in-out;
-moz-transition: -moz-box-shadow 0.45s, border-color 0.45s ease-in-out;
transition: box-shadow 0.45s, border-color 0.45s ease-in-out;
-webkit-transition: all 0.15s linear;
-moz-transition: all 0.15s linear;
-o-transition: all 0.15s linear;
font-size: 16px;
}

div#$optin_css_id.mo-baremetal-container #{$optin_css_id}_submit_button {
border: none;
font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
line-height: normal;
letter-spacing: normal;
margin: 16px 0 0;
position: relative;
text-decoration: none;
text-align: center;
text-transform: uppercase;
text-shadow: none;
box-shadow: none;
height: auto;
min-width: initial;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
outline: 0;
display: inline-block;
padding: 16px 32px 17px;
font-size: 16px;
background: #0073b7;
color: #ffffff;
-webkit-transition: background-color 1s;
-moz-transition: background-color 1s;
-o-transition: background-color 1s;
transition: background-color 1s;
width: 100%;
-webkit-border-radius: 3px;
border-radius: 3px;
float: initial;
cursor: pointer;
font-weight: 600;
}

div#$optin_css_id.mo-baremetal-container .mo-optin-error {
display: none;
background: #FF0000;
color: #ffffff;
text-align: center;
padding: .2em;
margin: 0;
width: 100%;
font-size: 16px;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box; 
}

div#$optin_css_id ul {
    margin: 0 0 1.6em 1.3333em;
}
CSS;

    }
}