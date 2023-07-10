<?php
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;



if (!defined('_PS_VERSION_')) {
    exit;
}

class WelcomeShop extends Module implements WidgetInterface
{


    public function __construct()
    {
        $this->name         = 'welcomeshop';
        $this->tab          = 'front_office_features';
        $this->version      = '1.0.0';
        $this->author       = 'idealive Consulting';
        $this->need_instance= 0;
        $this->bootstrap    = true;
        
        parent :: __construct();

        $this->displayName  = $this->l('Welcome Shop', $this->name);
        $this->description  = $this->l('Welcome text for guests in any part of the shop.', $this->name);

        $this->ps_versions_compliancy = array('min' => '1.7', 'max'=> _PS_VERSION_);

    }


    public function install()
    {
        return parent::install() 
                && $this->createConfigVars()
                && $this->registerHook('displayHeader')
                && $this->registerHook('displayWrapperTop')
                && $this->registerHook('displayHome')
                ;
    }


    public function uninstall()
    {
        return parent::uninstall() && $this->deleteConfigVars();
    }


    public function createConfigVars()
    {
        Configuration::updateValue('WELCOMESHOP_ONLY_LOGGED_OUT', 0);

        //Returns installed languages.
        $languages = Language::getLanguages(false);
        $values = array();
        foreach ($languages as $lang) {
            $values['WELCOMESHOP_TEXT'][(int)$lang['id_lang']] = '';
            Configuration::updateValue('WELCOMESHOP_TEXT', $values['WELCOMESHOP_TEXT'], true);
        }
        return true;
    }
    

    public function deleteConfigVars()
    {
        if (Configuration::deleteByName('WELCOMESHOP_TEXT') &&
            Configuration::deleteByName('WELCOMESHOP_ONLY_LOGGED_OUT')) {
            return true;
        }
        return false;
    }
    

    public function hookDisplayHeader(){
        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );
    }

    public function hookDisplayWrapperTop(){}

    public function hookDisplayHome(){}


    public function postProcess(){
        if (Tools::isSubmit('submitSettings')){
            // dump($_POST); exit();
            Configuration::updateValue('WELCOMESHOP_ONLY_LOGGED_OUT', Tools::getValue('WELCOMESHOP_ONLY_LOGGED_OUT'));
            $values = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang){                      //El POST gestiona el multilang WELCOMESHOP_TEXT_1 - WELCOMESHOP_TEXT_2
                $values['WELCOMESHOP_TEXT'][$lang['id_lang']] = (string)Tools::getValue('WELCOMESHOP_TEXT_'.$lang['id_lang']);
            }
            Configuration::updateValue('WELCOMESHOP_TEXT', $values['WELCOMESHOP_TEXT'], true);
            return $this->displayConfirmation($this->l('Settings updated ok.'));
        }
    }

    public function getContent(){
        $output = '';
        $output .= $this->postProcess();
        // return 'This is a configuration page.';
        $output .= $this->displayForm();
        return $output;
    }

    public function displayForm()
    {
        $languages = Language::getLanguages(false);
        //Establece cual el lenguaje por defecto
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');
        }
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitSettings';
    

        $this->fields_form[0]['form'] = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Welcome text'),
                    'name' => 'WELCOMESHOP_TEXT',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 40,
                    'rows' => 7,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display welcome text only when the user is not logged in'),
                    'name' => 'WELCOMESHOP_ONLY_LOGGED_OUT',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'name' => 'submitSettings',
                'title' => $this->l('Save'),
            ),
        );
        //Para Cada lenguaje
        //Carga el valor del campo TEXTO enriquecido
        foreach ($languages as $lang) {
                                                                    //getValue te 2 parametres: la clau que es concatenant l'idioma + valor per defecte
            $helper->fields_value['WELCOMESHOP_TEXT'][$lang['id_lang']] = Tools::getValue('WELCOMESHOP_TEXT_'.$lang['id_lang'],
                                                                         Configuration::get('WELCOMESHOP_TEXT', $lang['id_lang']));
        }
        //Carga el Valor de la BDs en el campo
        $helper->fields_value['WELCOMESHOP_ONLY_LOGGED_OUT'] = Configuration::get('WELCOMESHOP_ONLY_LOGGED_OUT');
       
        return $helper->generateForm($this->fields_form);
    }


    public function renderWidget($hookName = null, array $configuration = [])
    {
        // if ($this->context->cookie->id_customer && Configuration::get('WELCOMESHOP_ONLY_LOGGED_OUT') == 1) {
        //     return;
        // }
        
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch('module:'.$this->name.'/views/templates/hook/widget.tpl');
    }


    public function getWidgetVariables($hookName, array $params)
    {
        return [
            'WELCOMESHOP_ONLY_LOGGED_OUT' => Configuration::get('WELCOMESHOP_ONLY_LOGGED_OUT'),
            'WELCOMESHOP_TEXT' => Configuration::get('WELCOMESHOP_TEXT', $this->context->language->id),
        ];
    }
    
    
}