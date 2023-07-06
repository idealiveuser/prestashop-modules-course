<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class Helloworld extends Module
{

    public function __construct()
    {
        $this->name         = 'helloworld';
        $this->tab          = 'front_office_features';
        $this->version      = '1.0.0';
        
        parent :: __construct();

        $this->displayName  = $this->l('Hello World', 'helloworld');
        $this->description  = $this->l('My very first module, in Home Page.','helloworld');

    }


    public function install()
    {
        return parent::install() && $this->registerHook('displayHome','helloworld');

    }

    public function uninstall()
    {
        return parent::uninstall();
                // && $this->unregisterHook('displayHome');

    }


    public function hookDisplayHome()
    {
        return $this->display(__FILE__, 'home.tpl');
    }



}