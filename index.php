<?php

/**
 * moziloCMS Plugin: slideText
 *
 * Generates content, that shows further content on click.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2014-03-03
 * @link     https://github.com/devmount/slideText
 * @link     http://devmount.de/Develop/moziloCMS/Plugins/slideText.html
 * @see      The name of the LORD is a strong tower; the righteous run to it and are
 *           safe.
 *            - The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * slideText Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount/slideText
 */
class slideText extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'HPdesigner';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/moziloCMS/Plugins/slideText.html';
    const PLUGIN_TITLE   = 'slideText';
    const PLUGIN_VERSION = 'v0.1.2014-03-03';
    const MOZILO_VERSION = '2.0';
    private $_plugin_tags = array(
        'default' => '{slideText|toclick|toshow}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        'show_arrow' => array(
            true,
            'check',
        ),
        'duration' => array(
            '400',
            'text',
            '100',
            '10',
            "/^[0-9]{0,4}$/",
        ),
        'easing' => array(
            'swing',
            'select',
            array('swing','linear'),
            FALSE,
        ),
    );

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;

        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get params
        list($question, $answere) = explode('|', $value);
        $question = trim($question);
        $answere = trim($answere);

        // handle input
        if ($question == '') {
            return $this->throwError(
                $this->_cms_lang->getLanguageValue('error_toclick')
            );
        }
        if ($answere == '') {
            return $this->throwError(
                $this->_cms_lang->getLanguageValue('error_toshow')
            );
        }

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        // include jquery and slideText javascript
        $syntax->insert_jquery_in_head('jquery');
        $js = '';
        $js .= '<script type="text/javascript">
                    $(document).ready(function(){
                        $(".slidetext").click(function(event){';

        // show toggle arrow depending on configuration
        if ($conf['show_arrow'] == 'true') {
            $js .= '$(this).children(".question").children(".toggle-arrow")
                    .toggleClass("opened");';
        }

        $js .= '            $(this).children(".answere").slideToggle(
                                "' . $conf['duration'] . '",
                                "' . $conf['easing'] . '"
                            );
                        });
                    });
                </script>';
        $syntax->insert_in_head($js);

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

        // build return content
        $content .= '
            <div class="slidetext">'
            . '<span class="question">'
                . $question;

        // show toggle arrow depending on configuration
        if ($conf['show_arrow'] == 'true') {
            $content .= '<span class="toggle-arrow"></span>';
        }

        $content .= '
                </span>'
            . '<div class="answere">' . $answere . '</div>
            </div>
        ';

        // end plugin content
        $content .= '<!-- END ' . self::PLUGIN_TITLE . ' plugin content --> ';

        // return content
        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        $template .= '
            <div class="slidetext-admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content slidetext-admin-li">
            <div class="slidetext-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_display')
            . '</div>
            <div style="margin-bottom:5px;">
                {show_arrow_checkbox}
                {show_arrow_description}
                <span class="slidetext-admin-default">
                    [' . $this->_confdefault['show_arrow'][0] .']
                </span>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content slidetext-admin-li">
            <div class="slidetext-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_effect')
            . '</div>
            <div style="margin-bottom:5px;">
                {duration_text}
                {duration_description}
                <span class="slidetext-admin-default">
                    [' . $this->_confdefault['duration'][0] .']
                </span>
            </div>
            <div style="margin-bottom:5px;">
                {easing_description}
                <span class="slidetext-admin-default">
                    [' . $this->_confdefault['easing'][0] .']
                </span>
                <div class="select">{easing_select}</div>
        ';

        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['default'])
            ),
            self::PLUGIN_AUTHOR,
            self::PLUGIN_DOCU,
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     *
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'textarea',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     *
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     *
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     *
     * @param string $description Label
     *
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     *
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     *
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        );
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled message
     *
     * @param string $type Type of message ('ERROR', 'SUCCESS')
     * @param string $text Content of message
     *
     * @return string HTML content
     */
    protected function throwMessage($text, $type)
    {
        return '<div class="'
                . strtolower(self::PLUGIN_TITLE . '-' . $type)
            . '">'
            . '<div>'
                . $this->_cms_lang->getLanguageValue(strtolower($type))
            . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

}

?>