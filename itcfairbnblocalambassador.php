<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itcfairbnblocalambassador extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'itcfairbnblocalambassador';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Studio ITC';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('FairBNB Local Ambassador Dashboard');
        $this->description = $this->l('This module makes you create keypair');


        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('ITCFAIRBNBLOCALAMBASSADOR_LIVE_MODE', false);
        include _PS_MODULE_DIR_ . 'itcfairbnblocalambassador/sql/install.php';

        return parent::install() &&
            $this->installTab(
                'LocalAmbassador', 'itcfairbnblocalambassador_index', 'Local Ambassador Key Gen',
                'AdminAdvancedParameters'
            ) &&
            $this->registerHook('displayReassurance') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ITCFAIRBNBLOCALAMBASSADOR_LIVE_MODE');

        return parent::uninstall();
    }


    private function installTab($class, $route, $name, $parent)
    {
        $tabId = (int)Tab::getIdFromClassName($class);
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = $class;
        // Only since 1.7.7, you can define a route name
        $tab->route_name = $route;
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans($name, array(), 'Modules.MyModule.Admin', $lang['locale']);
        }
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab($class)
    {
        $tabId = (int)Tab::getIdFromClassName($class);
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }


    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJqueryPlugin(array('fancybox'));
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayReassurance($params)
    {
        $result = DB::getInstance()->getRow(
            "SELECT sc.`signed_contract`,k.public_key,k.id_employee 
FROM `" . _DB_PREFIX_ . "itc_signed_contracts` as sc 
JOIN " . _DB_PREFIX_ . "itc_keys as k on k.id_employee=sc.id_employee 
WHERE sc.`signed_contract` LIKE '%\"product_id\":\"" . Tools::getAllValues()['id_product'] . "\"%'"
        );

        $productInfoSignature = "productInfo.signature";

        $product_certificate=json_encode(json_decode($result['signed_contract']), JSON_PRETTY_PRINT);

        $this->context->smarty->assign(
            [
                "product_certificate" => $product_certificate,
            ]
        );

        if($product_certificate!="null"){
            $this->context->smarty->assign(
                [
                    "public_key"          => $result['public_key'],
                    "id_employee"         => $result['id_employee'],
                    "signature"           => json_encode(json_decode($result['signed_contract'])->$productInfoSignature, JSON_PRETTY_PRINT),
                    "product_certificate_js"           => $result['signed_contract'],
                ]
            );
        }

        return $this->display(__FILE__, 'itc_product.tpl');
    }
}
