<?php
/**
 * Module base
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors
 * #Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
 *
 * @author    Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license   commercial license see license.txt
 * @category  Prestashop
 * @category  Module
 */

/* $this->l('Error while downloading module.', 'Samdha_Commons_Module'); */
namespace Samdha\Module;

class Module extends \Module
{
    public $context;
    public $errors = array();
    public $warnings = array(); /* @since 1.2.0.0 */
    public $confirmations = array();
    public $config_arrays_keys = array();
    public $config_global = false; /* get configuration by shop */
    public $short_name = 'samdha';
    public $description_big = '';
    public $id_addons = false;

    const INSTALL_SQL_FILE = 'install.sql';
    const UNINSTALL_SQL_FILE = 'uninstall.sql';

    public $author = 'Samdha';
    public $need_instance = 0;
    public $toolbar_btn = array();
    public $samdha_tools;
    public $config;
    public $bootstrap = true;

    public function __construct()
    {
        /* Backward compatibility */
        $backward_file = __DIR__.'/../backward_compatibility/backward.php';
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<') && file_exists($backward_file)) {
            require($backward_file);
        }

        parent::__construct();
        $this->samdha_tools = new Tools($this);
        $this->config = new Configuration($this);
        $config_file = __DIR__.'/../config/config.ini';
        if (file_exists($config_file)) {
            $configs = parse_ini_file($config_file);
            foreach ($configs as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->support_url = 'https://addons.prestashop.com/contact-community.php?id_product=%d&content_only=1';
            $this->support_url .= '&lang=%s#contact-form';
            $this->home_url = 'https://addons.prestashop.com/%s/2_community?contributor=5716&utm_source=module';
            $this->home_url .= '&utm_medium=prestashop&utm_content=homelink&utm_campaign=%s';
            $this->contact_url = 'https://addons.prestashop.com/contact-community.php?id_product=%d&utm_source=module';
            $this->contact_url .= '&utm_medium=prestashop&utm_content=contactlink&utm_campaign=%s#contact-form';
            $this->rate_url = 'http://addons.prestashop.com/ratings.php';
            $this->products_url = 'https://addons.prestashop.com/%s/2_community-developer?contributor=5716';
        }
        $this->hook_path = (version_compare(_PS_VERSION_, '1.5.0.0', '<')?'/views/templates/hook/':'');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->config->delete();
    }

    public function postProcess($token)
    {
        $module_url = \AdminController::$currentIndex.'&module_name='
            .$this->name.'&configure='.$this->name.'&token='.$token;

        if (\Tools::isSubmit('getDocumentation')) {
            $this->displayDocumentation();
        }

        if (\Tools::isSubmit('saveSettings')) {
            $this->config->update($this->samdha_tools->stripSlashesArray(\Tools::getValue('setting')));
            $url = $module_url.'&conf=6';
            if (\Tools::getValue('active_tab')) {
                $url .= '&active_tab='.\Tools::getValue('active_tab');
            }
            \Tools::redirectAdmin($url);
        }

        if (\Tools::getValue('postUpdateModule')) {
            $this->postUpdateModule();
            \Tools::redirectAdmin($module_url.'&conf=4');
        }
    }

    public function getContent($tab = 'AdminModules')
    {
        $cookie = $this->context->cookie;

        if (version_compare(_PS_VERSION_, '1.4.9.0', '<')) {
            if (method_exists($this->context->smarty, 'register_modifier')) {
                $this->context->smarty->unregister_modifier('unescape');
                $this->context->smarty->register_modifier(
                    'unescape',
                    array($this->samdha_tools, 'smartyModifiercompilerUnescape')
                );
            } else {
                $this->context->smarty->unregisterPlugin('modifier', 'unescape');
                $this->context->smarty->registerPlugin(
                    'modifier',
                    'unescape',
                    array($this->samdha_tools, 'smartyModifiercompilerUnescape')
                );
            }
        }
        $this->context->smarty->registerPlugin('function', 'samdha_l', 'smartyTranslate');

        // load generic translations
        if (is_object($cookie) && isset($cookie->id_lang)) {
            $iso_lang = \Language::getIsoById($cookie->id_lang);
            $file = __DIR__.'/../translations'.DIRECTORY_SEPARATOR.$iso_lang.'.php';
            if (file_exists($file)) {
                include($file);
            }
        }

        if (method_exists('Tools', 'getAdminToken') && isset($cookie->id_employee)) {
            $token = \Tools::getAdminToken($tab.(int) \Tab::getIdFromClassName($tab).(int) $cookie->id_employee);
        } else {
            $token = 1;
        }

        $this->context->smarty->compile_check = true;
        $this->postProcess($token);

        if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            $output = '<h2>'.$this->displayName.'</h2>';
        } elseif (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $output = $this->displayToolBar(\AdminController::$currentIndex, $token);
        } else {
            $output = '';
            if (is_object($this->context->controller) && is_array($this->context->controller->css_files)) {
                $files = array_keys($this->context->controller->css_files);
                foreach ($files as $file) {
                    if (strpos($file, 'base/jquery.ui.theme.css') !== false) {
                        unset($this->context->controller->css_files[$file]);
                        break;
                    }
                }
            }
        }

        $output .= $this->displayConfirmations($this->confirmations);
        $output .= $this->displayWarnings($this->warnings); // @since 1.2.0.0
        $output .= $this->displayErrors($this->errors);

        $output .= $this->displayForm($token);

        return $output;
    }

    private function displayToolBar($current_index, $token)
    {
        $back = $current_index.'&token='.$this->context->controller->token.'&module_name='.$this->name;
        $back .= '&tab_module='.$this->tab.'&anchor=anchor'.\Tools::ucfirst($this->name);
        $this->toolbar_btn['back'] = array(
            'href' => $back,
            'desc' => $this->l('Back to list', 'Module')
        );

        $this->context->smarty->assign(array(
            'toolbar_btn' => $this->toolbar_btn,
            'toolbar_scroll' => true,
            'title' => $this->displayName,
            'table' => $this->short_name
        ));
        return $this->context->smarty->fetch('toolbar.tpl');
    }

    private function displayErrors($messages)
    {
        $output = '';
        if (!empty($messages)) {
            if (method_exists($this, 'displayError')) {
                foreach ($messages as $message) {
                    $output .= $this->displayError($message);
                }
            } else {
                if (version_compare(_PS_VERSION_, '1.4.0.0', '<')) {
                    $ps_admin_img = 'https://addons.prestashop.com/img/admin/';
                } else {
                    $ps_admin_img = _PS_ADMIN_IMG_;
                }
                $this->context->smarty->assign(array(
                    'messages_html' => $messages,
                    'ps_admin_img'  => $ps_admin_img
                ));
                $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$this->name.'.php';
                $template = 'vendor/samdha/module/views/templates/admin/samdha_error.tpl';
                $output = $this->display($filename, $template);
            }
        }
        return $output;
    }

    /**
     * @since 1.2.0.0
     */
    private function displayWarnings($messages)
    {
        $output = '';
        if (!empty($messages)) {
            if (method_exists($this, 'displayWarning')) {
                foreach ($messages as $message) {
                    $output .= $this->displayWarning($message);
                }
            } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && $this->bootstrap) {
                foreach ($messages as $message) {
                    $this->context->controller->warnings[] = $message;
                }
            } else {
                if (version_compare(_PS_VERSION_, '1.4.0.0', '<')) {
                    $ps_admin_img = 'https://addons.prestashop.com/img/admin/';
                } else {
                    $ps_admin_img = _PS_ADMIN_IMG_;
                }
                $this->context->smarty->assign(array(
                    'version_14'    => version_compare(_PS_VERSION_, '1.4.0.0', '>='),
                    'version_15'    => version_compare(_PS_VERSION_, '1.5.0.0', '>='),
                    'messages_html' => $messages,
                    'ps_admin_img'  => $ps_admin_img
                ));
                $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$this->name.'.php';
                $template = 'vendor/samdha/module/views/templates/admin/samdha_warning.tpl';
                $output = $this->display($filename, $template);
            }
        }
        return $output;
    }

    private function displayConfirmations($messages)
    {
        $output = '';
        if (!empty($messages)) {
            if (method_exists($this, 'displayConfirmation')) {
                foreach ($messages as $message) {
                    $output .= $this->displayConfirmation($message);
                }
            } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && $this->bootstrap) {
                foreach ($messages as $message) {
                    $this->context->controller->confirmations[] = $message;
                }
            } else {
                if (version_compare(_PS_VERSION_, '1.4.0.0', '<')) {
                    $ps_admin_img = 'https://addons.prestashop.com/img/admin/';
                } else {
                    $ps_admin_img = _PS_ADMIN_IMG_;
                }
                $this->context->smarty->assign(array(
                    'version_14'    => version_compare(_PS_VERSION_, '1.4.0.0', '>='),
                    'version_15'    => version_compare(_PS_VERSION_, '1.5.0.0', '>='),
                    'messages_html' => $messages,
                    'ps_admin_img'  => $ps_admin_img
                ));
                $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$this->name.'.php';
                $template = 'vendor/samdha/module/views/templates/admin/samdha_confirmation.tpl';
                $output = $this->display($filename, $template);
            }
        }
        return $output;
    }

    public function displayForm($token)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            $content = ob_get_clean();
            $new_content = preg_replace(
                '|<link type="text/css" rel="stylesheet" href="[^"]*datepicker.css" />|',
                '',
                $content
            );
            echo $new_content;
            ob_start();
        }

        $about_form = $this->displayAboutForm();

        $templates_path = _PS_MODULE_DIR_.$this->name.'/views/templates/';

        if (file_exists($templates_path.'admin/admin.tpl')) {
            $content = $templates_path.'admin/admin.tpl';
        } elseif (file_exists($templates_path.'hook/admin.tpl')) {
            $content = $templates_path.'hook/admin.tpl';
        } else {
            $content = false;
        }
        if (file_exists($templates_path.'admin/admin_footer.tpl')) {
            $footer = $templates_path.'admin/admin_footer.tpl';
        } elseif (file_exists($templates_path.'hook/admin_footer.tpl')) {
            $footer = $templates_path.'hook/admin_footer.tpl';
        } else {
            $footer = false;
        }

        $module_url = \AdminController::$currentIndex.'&configure='.urlencode($this->name).'&token='.$token;

        $this->smarty->assign(array(
                'about_form_html'   => $about_form,
                'module_config'     => $this->config->getAsArray(),
                'module_short_name' => $this->short_name,
                'module_url'        => $module_url,
                'module_path'       => '//'.$this->samdha_tools->getHttpHost(false).$this->_path,
                'vendor_path'       => '//'.$this->samdha_tools->getHttpHost(false).$this->_path.'vendor/samdha/module/',
                'module_directory'  => _PS_MODULE_DIR_.$this->name,
                'active_tab'        => \Tools::getValue('active_tab'),
                'support_url'       => $this->getSupportURL(),
                'documentation_url' => $module_url.'&getDocumentation=1',
                'rate_url'          => $this->getRateURL(),
                'products_url'      => $this->getProductsURL(),
                'version_14'        => version_compare(_PS_VERSION_, '1.4.0.0', '>='),
                'version_15'        => version_compare(_PS_VERSION_, '1.5.0.0', '>='),
                'version_16'        => version_compare(_PS_VERSION_, '1.6.0.0', '>='),
                'version_17'        => version_compare(_PS_VERSION_, '1.7.0.0', '>='),
                'bootstrap'         => $this->bootstrap,
                'module_version'    => $this->version,

                'content'           => $content,
                'footer'            => $footer,
                'admin_js'          => file_exists(_PS_MODULE_DIR_.$this->name.'/views/js/admin.js')
        ));
        // Display Form

        $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$this->name.'.php';
        $template = 'vendor/samdha/module/views/templates/admin/samdha_admin.tpl';
        $output = $this->display($filename, $template);
        return $output;
    }

    public function getSupportURL()
    {
        $iso_lang = \Language::getIsoById($this->context->cookie->id_lang);
        return sprintf(
            $this->support_url,
            $this->id_addons,
            $iso_lang
        );
    }

    public function displayAboutForm()
    {
        $smarty = isset($this->smarty) ? $this->smarty : $this->context->smarty;

        // for translation with Prestashop 1.x
        if (version_compare(_PS_VERSION_, '1.2.0.0', '<')) {
            $tmp_page = $this->page;
            $this->page = 'Module';
        }

        $iso_lang = \Language::getIsoById($this->context->cookie->id_lang);
        if (!in_array($iso_lang, array('en', 'fr', 'es', 'de', 'it', 'nl', 'pl', 'pt', 'ru'))) {
            $iso_lang = 'en';
        }
        $home_url = sprintf($this->home_url, $iso_lang, urlencode($this->name));
        $contact_url = sprintf($this->contact_url, $this->id_addons, urlencode($this->name));

        $smarty->assign(array(
            'version_14'          => version_compare(_PS_VERSION_, '1.4.0.0', '>='),
            'version_16'          => version_compare(_PS_VERSION_, '1.6.0.0', '>='),
            'module_path'         => $this->_path,
            'module_display_name' => $this->displayName,
            'module_version'      => $this->version,
            'description_big_html'=> $this->description_big,
            'description'         => $this->description,
            'bootstrap'           => $this->bootstrap,
            'home_url'            => $home_url,
            'contact_url'         => $contact_url
        ));

        $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$this->name.'.php';
        $template = 'vendor/samdha/module/views/templates/admin/samdha_aboutform.tpl';
        $output = $this->display($filename, $template);

        // for translation with Prestashop 1.x
        if (version_compare(_PS_VERSION_, '1.2.0.0', '<')) {
            $this->page = $tmp_page;
        }

        return $output;
    }

    /**
     * return rate URL for this module
     *
     * @return string
     */
    public function getRateURL()
    {
        if (!$this->id_addons) {
            return false;
        }
        
        $iso_lang = \Language::getIsoById($this->context->cookie->id_lang);
        return sprintf(
            $this->rate_url,
            $iso_lang,
            $this->id_addons
        );
    }

    /**
     * return products URL for this module
     *
     * @return string
     */
    public function getProductsURL()
    {
        $iso_lang = \Language::getIsoById($this->context->cookie->id_lang);
        if (!in_array($iso_lang, array('en', 'fr', 'es', 'de', 'it', 'nl', 'pl', 'pt', 'ru'))) {
            $iso_lang = 'en';
        }
        return sprintf(
            $this->products_url,
            $iso_lang
        );
    }

    public function displayDocumentation()
    {
        $folder = isset($this->local_path)?$this->local_path:_PS_MODULE_DIR_.$this->name.'/';
        $folder .= 'documentation/';

        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        if (!is_dir($folder)) {
            die();
        }

        $iso_lang = \Language::getIsoById($this->context->cookie->id_lang);

        /* Display documentation */
        if ($iso_lang) {
            // don't use glob() it can be blocked for "security reason"
            $files = array_diff(scandir($folder), array('..', '.'));
            foreach ($files as $filename) {
                if ($filename == $iso_lang.'.html') {
                    $html = $this->samdha_tools->fileGetContents($folder.$filename);
                    echo str_replace(' src="../', ' src="'.$this->_path, $html);
                    die();
                }
            }
        }
        if (file_exists($folder.'en.html')) {
            $html = $this->samdha_tools->fileGetContents($folder.'en.html');
            echo str_replace(' src="../', ' src="'.$this->_path, $html);
        }
        die();
    }

    /**
     * set default config
     */
    public function getDefaultConfig()
    {
        return array();
    }

    public function postSaveConfig()
    {
        // do stuff
    }

    public function postUpdateModule()
    {
        // clean cache, update BDD...
    }

    /**
     * idem than Module::l but with $id_lang
     * @since 1.1.0.0
     * */
    public function l($string, $specific = false, $id_lang = null)
    {
        if (is_null($id_lang) ||
            (version_compare(_PS_VERSION_, '1.4.0.0', '>=') && version_compare(_PS_VERSION_, '1.5.0.0', '<'))
        ) {
            return parent::l($string, $specific, $id_lang);
        }

        $iso_lang = \Language::getIsoById($id_lang);

        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $file = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'translations'.DIRECTORY_SEPARATOR.$iso_lang.'.php';
            if (!file_exists($file)) {
                $file = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$iso_lang.'.php';
            }
        } else {
            $file = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.$iso_lang.'.php';
        }

        if (method_exists('Tools', 'file_exists_cache')) {
            if (\Tools::file_exists_cache($file)) {
                include($file);
            }
        } elseif (file_exists($file)) {
            include($file);
        }

        $var_module = '_MODULE'; // validator
        if (isset($$var_module)) {
            $module = $$var_module;
            $string2 = str_replace('\'', '\\\'', $string);
            $source = \Tools::strtolower($specific ? $specific : get_class($this));
            $current_key = '<{'.$this->name.'}'._THEME_NAME_.'>'.$source.'_'.md5($string2);
            $default_key = '<{'.$this->name.'}prestashop>'.$source.'_'.md5($string2);

            if (key_exists($current_key, $module)) {
                $ret = $this->samdha_tools->stripSlashes($module[$current_key]);
            } elseif (key_exists($default_key, $module)) {
                $ret = $this->samdha_tools->stripSlashes($module[$default_key]);
            } else {
                $ret = $string;
            }
        } else {
            $ret = $string;
        }

        $ret = str_replace('"', '&quot;', $ret);
        return $ret;
    }
}
