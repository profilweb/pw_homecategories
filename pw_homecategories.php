<?php
/**
* PW Homecategories
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    Profil Web
* @copyright Copyright 2021 ©profilweb All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @package   pw_homecategories
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

Class Pw_homecategories extends Module
{
    public function __construct()
    {
        $this->name = 'pw_homecategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Profil Web';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PW Home Categories');
        $this->description = $this->l('Display categories info on homepage');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() || !$this->registerHook('displayHome')) {
            return false;
        }
 
        return true;
    }

    public function hookDisplayHome()
    {

        global $link;

        $idLanguage = (int) Context::getContext()->language->id;

        $categories = Category::getChildren(2, $idLanguage, true);
        foreach ($categories as $i => $category) {
            $categories[$i] = new Category($category['id_category'], $idLanguage);
        }

        $this->context->smarty->assign([
            'categories'        => $categories,
            'link'              => $link
        ]);

        return $this->display(__FILE__, 'pw_homecategories.tpl');
    }
}
