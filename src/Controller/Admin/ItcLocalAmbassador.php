<?php

namespace StudioItc\ItcFairBNBLocalAmbassador\Controller\Admin;

use Context;
use Db;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Product;
use Tools;

/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Ilenia Amadori <sviluppoweb@studioitc.com>
 * @copyright Copyright (c) 2018 Studio ITC Srl - www.studioitc.com
 * @license   You only can use module, nothing more!
 */
class ItcLocalAmbassador extends FrameworkBundleAdminController
{

    public function index()
    {
        $context = Context::getContext();
        $id_employee = $context->cookie->getAll()['id_employee'];

        $db = DB::getInstance();
        $first_fancybox_page = $this->render(
            '@Modules/itcfairbnblocalambassador/views/templates/admin/first_fancybox_page.html.twig',
            [
                'id_employee' => $id_employee,
            ]
        );

        $result = $db->getValue(
            "SELECT id_employee FROM " . _DB_PREFIX_ . "itc_keys WHERE id_employee=" . $id_employee
        );

        $productObj = new Product(21);
        $products = $productObj->getProducts(1, 0, 0, 'id_product', 'DESC');


        return $this->render(
            '@Modules/itcfairbnblocalambassador/views/templates/admin/index.html.twig',
            [
                'id_employee'         => $id_employee,
                'first_fancybox_page' => json_encode(str_replace("\n", "", $first_fancybox_page->getContent())),
                'canSign'             => $result ? true : false,
                'products'            => $products,
            ]
        );
    }

    public function checkAnswers()
    {
        $db = DB::getInstance();
        $return = 0;
        $val = Tools::getAllValues();


        $sfContainer = call_user_func(array('\PrestaShop\PrestaShop\Adapter\SymfonyContainer', 'getInstance'));
        if (null !== $sfContainer) {
            $sfRouter = $sfContainer->get('router');
            $link = $sfRouter->generate(
                'itcfairbnblocalambassador_generate'
            );
        }


        $result =
            $db->getValue("SELECT answers FROM `" . _DB_PREFIX_ . "itc_questions` WHERE id_employee=" . $val['id']);
        if (!$result) {
            $db->execute(
                "INSERT INTO `" . _DB_PREFIX_ . "itc_questions` (`id_employee`, `answers`, `date`) VALUES ('" .
                $val['id'] . "', '" . $val["answers"]["myHashedAnswers"] . "', '" . date('Y-m-d H:i:s') . "')"
            );
            $return = 1;
        } else {
            if ($result == $val["answers"]["myHashedAnswers"]) {
                $return = 1;
            }
        }

        $encMessages = md5($val["answers"]["myHashedAnswers"]);

        $ajaxReturn = [
            $return,
            $link,
            $encMessages,
        ];

        die(json_encode($ajaxReturn));

    }


    public function generateKey()
    {
        $db = DB::getInstance();
        $return = 0;
        $val = Tools::getAllValues();


        $result = $db->getValue(
            "SELECT id_employee FROM " . _DB_PREFIX_ . "itc_keys WHERE id_employee=" . $val['id'] .
            " AND public_key='" .
            $val['public_key'] . "'"
        );

        if (!$result) {
            $db->execute(
                "INSERT INTO `" . _DB_PREFIX_ . "itc_keys` (`public_key`, `id_employee`, `date`) VALUES ('" .
                $val['public_key'] . "', '" . $val['id'] . "', '" . date('Y-m-d H:i:s') . "')"
            );
        }

        $return = 1;

        die(json_encode($return));
    }


    public function signContract()
    {

        $var = Tools::getAllValues();
        $db = DB::getInstance();
        $return = 0;

        if ($var["action"] == "pre-contract") {
            $product = new Product((int)$var['product_id']);
            $features = $product->getFrontFeatures(1);
            foreach($features as $key => $feature){
                unset($features[$key]['position']);
            }
            $return = [
                1,
                $features,
            ];
            die(json_encode($return));
        }else{
            if(!$db->getRow("SELECT * FROM `ps_itc_signed_contracts` WHERE `contract` LIKE '".json_encode($var['obj'])."'")){
                if ($db->execute(
                    "INSERT INTO `" . _DB_PREFIX_ .
                    "itc_signed_contracts` (`id_employee`, `contract`, `signed_contract`, `date`) VALUES ('" . $var['id'] .
                    "', '" . json_encode($var['obj']) . "', '" . json_encode($var['signed_obj']) . "', '" .
                    date('Y-m-d H:i:s') . "')"
                )) {
                    $return = 1;
                }
            }else{
                $return = 2;
            }
            die(json_encode($return));
        }
    }

}