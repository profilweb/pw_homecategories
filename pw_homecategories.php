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

Class PW_HomeCategories extends Module
{
    public function __construct()
    {
        $this->name = 'pw_homecategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'profilweb';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => '1.7.99',
        ];
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('Catégories sur la page d\'accueil');
        $this->description = $this->l('Affiche des blocs catégories sur la page d\'Accueil');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        /*if (!Configuration::get('PW_HOMECATEGORIES_CATALOG'))
            $this->warning = $this->l('No name provided.');*/
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

       return (
            parent::install() 
            && $this->registerHook('displayHome')
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('PW_HOMECATEGORIES_CATALOG', '2')
            && Configuration::updateValue('PW_HOMECATEGORIES_IMAGE_TYPE', 'small_default')
            && Configuration::updateValue('PW_HOMECATEGORIES_LIMIT', '6')
        ); 
    }

    public function uninstall()
    {
        return (
            parent::uninstall() 
            && Configuration::deleteByName('PW_HOMECATEGORIES_CATALOG')
            && Configuration::deleteByName('PW_HOMECATEGORIES_IMAGE_TYPE')
            && Configuration::deleteByName('PW_HOMECATEGORIES_LIMIT')
        );
    }

    /**
     * This method handles the module's configuration page
     * @return string The page's HTML content 
     */
    public function getContent()
    {
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $configValue = array(
                'PW_HOMECATEGORIES_CATALOG'     => (int) Tools::getValue('PW_HOMECATEGORIES_CATALOG'),
                'PW_HOMECATEGORIES_IMAGE_TYPE'  => (string) Tools::getValue('PW_HOMECATEGORIES_IMAGE_TYPE'),
                'PW_HOMECATEGORIES_LIMIT'       => (int) Tools::getValue('PW_HOMECATEGORIES_LIMIT')
            );

            // check that the value is valid
            if (empty($configValue)) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration value'));

            } else {
                // value is ok, update it and display a confirmation message
                foreach($configValue as $k => $v) {
                    Configuration::updateValue($k, $v);
                }

                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    /**
    * Builds the configuration form
    * @return string HTML code
    */
    public function displayForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],

                'succcess'    => $this->l('Form saved!'),
                'error'       => $this->l('Oops, something went wrong.'),

                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Root category of children categories to display'),
                        'desc' => $this->l('Choose a root category (default : Home category).'), 
                        'name' => 'PW_HOMECATEGORIES_CATALOG',
                        'size' => 20,
                        'required' => true,
                        'options' => array(
                            'query' => $categories,
                            'id' => 'id',
                            'name' => 'name' 
                        )
                    ],
                ],

                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Image size'),
                        'desc' => $this->l('See the configuration in "Design / Image settings" '), 
                        'name' => 'PW_HOMECATEGORIES_IMAGE_TYPE',
                        'required' => true,
                        'options' => array(
                            'query' => $imageTypes,
                            'id' => 'id',
                            'name' => 'name' 
                        )
                    ],
                ],

                'input' => [
                    [
                        'type' => 'number',
                        'label' => $this->l('The number of categories to display'),
                        'desc' => $this->l('The number of categories to display on homepage. Default: 6.'), 
                        'name' => 'PW_HOMECATEGORIES_LIMIT',
                        'size' => 20,
                        'required' => true,
                    ],
                ],

                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // GET CATEGORIES
        $categories = array();
        foreach (Category::getRootCategory((int)Context::getContext()->language->id) as $cat)
        {
            $categories[] = array(
                "id" => (int)$cat->id,
                "name" => $cat->name
            );
        }

        // GET CATEGORIES
        $imageTypes = array();
        foreach (ImageType::getImagesTypes('categories', true)) as $imgType)
        {
            $imageTypes[] = array(
                "id" => (int)$imgType->id,
                "name" => $imgType->name
            );
        }

        // Load current value into the form
        $helper->fields_value['PW_HOMECATEGORIES_CATALOG'] = Tools::getValue('PW_HOMECATEGORIES_CATALOG', Configuration::get('PW_HOMECATEGORIES_CATALOG'));
        $helper->fields_value['PW_HOMECATEGORIES_IMAGE_TYPE'] = Tools::getValue('PW_HOMECATEGORIES_IMAGE_TYPE', Configuration::get('PW_HOMECATEGORIES_IMAGE_TYPE'));
        $helper->fields_value['PW_HOMECATEGORIES_LIMIT'] = Tools::getValue('PW_HOMECATEGORIES_LIMIT', Configuration::get('PW_HOMECATEGORIES_LIMIT'));

        return $helper->generateForm([$form]);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'pw-homecategories-style',
            $this->_path.'views/css/pw-homecategories.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );
    }

    public function hookHome($params)
    {

        global $link;

        $idLanguage = (int) Context::getContext()->language->id;

        $categories = Category::getChildren((int) Configuration::get('HOMECATEGORIEZ_CATALOG'), $idLanguage, true);
        foreach ($categories as $i => $category) {
            $categories[$i] = new Category($category['id_category'], $idLanguage);
        }

        $limit = (int) Configuration::get('HOMECATEGORIEZ_LIMIT');
        if ($limit > 0) {
            $categories = array_splice($categories, 0, $limit);
        }

        $this->context->smarty->assign([
            'categories'        => $categories,
            'link'              => $link,
            'pic_size_type'     => Configuration::get('PW_HOMECATEGORIES_IMAGE_TYPE')
        ]);

        return $this->display(__FILE__, 'pw_homecategories.tpl');
    }
}
