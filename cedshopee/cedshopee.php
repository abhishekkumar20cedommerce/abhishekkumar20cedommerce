<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   Cedshopee
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
if (!function_exists('curl_version')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php');
include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeOrder.php');
include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProduct.php');

class CedShopee extends Module
{
    public $fields_form = array();
    public function __construct()
    {
        $this->name = 'cedshopee';
        $this->tab = 'administration';
        $this->version = '0.0.5';
        $this->author = 'CedCommerce';
        $this->bootstrap = true;
        $this->need_instance = 1;
        $this->module_key = 'a5e9830e9ca4ef5b71ca0d3b7f5839ac';

        $this->controllers = array('validation');
        $this->secure_key = Tools::encrypt($this->name);
        $this->is_eu_compatible = 1;
        $this->currencies = false;
        $this->displayName = $this->l('Shopee Integration');
        $this->description = $this->l(
            'Allow merchants to integrate their Prestashop shop with Shopee marketplace.'
        );
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
        parent::__construct();

        if (Tools::getIsset(Tools::getValue(array('action'))) &&
            Tools::getIsset(Tools::getValue(array('message'))) &&
            (trim(Tools::getValue(array('action'))) == 'validateResult')
        ) {
            $this->validateResult($_POST);
        }
    }

    public function install()
    {
        require_once _PS_MODULE_DIR_ . 'cedshopee/sql/installTables.php';
        if (!parent::install()
            || !$this->installTab(
                'AdminCedShopee',
                'Shopee Integration V2',
                0
            )
            || !$this->installTab(
                'AdminCedShopeeCategory',
                'Category',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeProfile',
                'Profile',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeBrand',
                'Brand Mapping',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeProduct',
                'Products',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeBulkUploadProduct',
                'Bulk Upload',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeUpdateStatus',
                'Update Status',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeOrder',
                'Orders',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeSyncOrderStatus',
                'Sync Order Status',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeFailedOrder',
                'Failed Orders',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeLogs',
                'Logs',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeLogistics',
                'Logistics',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeReturn',
                'Return',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeDiscount',
                'Discount',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->installTab(
                'AdminCedShopeeConfig',
                'Configuration',
                (int)Tab::getIdFromClassName('AdminCedShopee')
            )
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionUpdateQuantity')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('displayBackOfficeHeader')
        ) {
            return false;
        }
        if (!Configuration::get('PS_ORDER_RETURN')) {
            Configuration::updateValue('PS_ORDER_RETURN', 1);
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->uninstallTab('AdminCedShopee')
            || !$this->uninstallTab('AdminCedShopeeCategory')
            || !$this->uninstallTab('AdminCedShopeeProfile')
            || !$this->uninstallTab('AdminCedShopeeProduct')
            || !$this->uninstallTab('AdminCedShopeeBulkUploadProduct')
            || !$this->uninstallTab('AdminCedShopeeUpdateStatus')
            || !$this->uninstallTab('AdminCedShopeeOrder')
            || !$this->uninstallTab('AdminCedShopeeSyncOrderStatus')//New_ADDED
            || !$this->uninstallTab('AdminCedShopeeFailedOrder')
            || !$this->uninstallTab('AdminCedShopeeLogs')
            || !$this->uninstallTab('AdminCedShopeeLogistics')
            || !$this->uninstallTab('AdminCedShopeeReturn')
            || !$this->uninstallTab('AdminCedShopeeDiscount')
            || !$this->uninstallTab('AdminCedShopeeConfig')
            || !$this->unregisterHook('displayBackOfficeHeader')
            || !$this->unregisterHook('actionProductUpdate')
            || !$this->unregisterHook('actionProductDelete')
            || !$this->unregisterHook('actionUpdateQuantity')
            || !$this->unregisterHook('actionOrderStatusPostUpdate')
        ) {
            return false;
        }
        if (!Configuration::get('PS_ORDER_RETURN')) {
            Configuration::updateValue('PS_ORDER_RETURN', 1);
        }
        return true;
    }
    /* install tabs on basis of class name given
    * use tab name in frontend
    * install under the parent tab given
    */
    public function installTab($class_name, $tab_name, $parent)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $class_name;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }
        if ($parent == 0 && _PS_VERSION_ >= '1.7') {
            $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
            $tab->icon = 'flight';
        } else {
            $tab->id_parent = $parent;
        }
        $tab->module = $this->name;
        return $tab->add();
    }
    /**
     * uninstall tabs created by module
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function initContent()
    {
        if (Tools::getIsset('ajax') && Tools::getValue('ajax')) {
            $this->ajax = true;
        }
        parent::initContent();
    }
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCedshopeeModule')) == true) {
            $this->postProcess();

            if (Tools::getValue('CEDSHOPEE_MODE') == '1') {
                if (Tools::getValue('CEDSHOPEE_LIVE_PARTNER_ID') == ''
                    || Tools::getValue('CEDSHOPEE_LIVE_PARTNER_ID') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Live Partner ID');
                }

                if (Tools::getValue('CEDSHOPEE_LIVE_SHOP_ID') == ''
                    || Tools::getValue('CEDSHOPEE_LIVE_SHOP_ID') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Live Shop ID');
                }

                if (Tools::getValue('CEDSHOPEE_LIVE_SIGNATURE') == ''
                    || Tools::getValue('CEDSHOPEE_LIVE_SIGNATURE') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Live Signature');
                }
            } else {
                if (Tools::getValue('CEDSHOPEE_SANDBOX_PARTNER_ID') == ''
                    || Tools::getValue('CEDSHOPEE_SANDBOX_PARTNER_ID') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Sandbox Partner ID');
                }

                if (Tools::getValue('CEDSHOPEE_SANDBOX_SHOP_ID') == ''
                    || Tools::getValue('CEDSHOPEE_SANDBOX_SHOP_ID') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Sandbox Shop ID');
                }

                if (Tools::getValue('CEDSHOPEE_SANDBOX_SIGNATURE') == ''
                    || Tools::getValue('CEDSHOPEE_SANDBOX_SIGNATURE') == null
                ) {
                    $output .= $this->displayError('Please Fill Shopee Sandbox Signature');
                }
            }

            if (Tools::getValue('CEDSHOPEE_CUSTOMER_ID')
                && !empty(Tools::getValue('CEDSHOPEE_CUSTOMER_ID'))
            ) {
                if (!Validate::isInt(Tools::getValue('CEDSHOPEE_CUSTOMER_ID'))) {
                    $output .= $this->displayError($this->l('Customer ID must be numeric'));
                    Configuration::updateValue('CEDSHOPEE_CUSTOMER_ID', '');
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation("Shopee configuration saved successfully.");
            }
        }

        $this->context->smarty->assign(array(
            'docs' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/' . $this->name .
                '/docs/shopee-prestashop-integration-user-manual-319.pdf'
        ));
        $this->context->smarty->assign('module_dir', $this->_path);

        $output1 = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $output1 . $this->getConfigForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    public function getConfigForm()
    {
        $fields_form = array();
        $fields_form[0]['form'] = $this->getGeneralSettingForm();
        $fields_form[1]['form'] = $this->getApiSettingForm();
        $fields_form[2]['form'] = $this->getProductSettingForm();
        $fields_form[3]['form'] = $this->getOrderSettingForm();
        $fields_form[4]['form'] = $this->getCronInfoForm();
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCedshopeeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm($fields_form);
    }
    /*
    * General form details
    */
    public function getGeneralSettingForm()
    {
        return array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable'),
                    'name' => 'CEDSHOPEE_ENABLE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Debug Mode'),
                    'name' => 'CEDSHOPEE_DEBUG_MODE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
            ),
        );
    }

    public function getApiSettingForm()
    {
        $redirect_uri = Context::getContext()->link->getModuleLink(
            'cedshopee',
            'success',
            array()
        );

        $this->context->smarty->assign(array(

            'live_url' => 'https://partner.shopeemobile.com/api/v2/',
            'sandbox_url' => 'https://partner.test-stable.shopeemobile.com/api/v2/',
            'redirect_uri' => $redirect_uri,
            'timestamp' => (int)time(),
            'token' => Tools::getAdminTokenLite('AdminModules'),
        ));

        $this->context->smarty->assign(array(
            'api_mode' => Configuration::get('CEDSHOPEE_MODE'),
            'live_api_url' => Configuration::get('CEDSHOPEE_LIVE_API_URL'),
            'live_partner_id' => Configuration::get('CEDSHOPEE_LIVE_PARTNER_ID'),
            'live_shop_id' => Configuration::get('CEDSHOPEE_LIVE_SHOP_ID'),
            'live_signature' => Configuration::get('CEDSHOPEE_LIVE_SIGNATURE'),
            'sandbox_api_url' => Configuration::get('CEDSHOPEE_SANDBOX_API_URL'),
            'sandbox_partner_id' => Configuration::get('CEDSHOPEE_SANDBOX_PARTNER_ID'),
            'sandbox_shop_id' => Configuration::get('CEDSHOPEE_SANDBOX_SHOP_ID'),
            'sandbox_signature' => Configuration::get('CEDSHOPEE_SANDBOX_SIGNATURE')
        ));

        $this->context->smarty->assign(array(
            'access_token' => Configuration::get('CEDSHOPEE_ACCESS_TOKEN'),
            'authorize_code' => Configuration::get('CEDSHOPEE_CODE'),
        ));

        $api_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/api_settings.tpl'
        );

        return array(
            'legend' => array(
                'title' => $this->l('API Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type'  => 'html',
                    'col' => 6,
                    'label'  => $this->l(''),
                    'name' => $api_html,
                )
            )
        );
    }
    /*
    * Product form details
    */

    public function ajaxProcessGetAccessToken()
    {
        $param = Tools::getAllValues();
        $data = array(
            'code' => $param['authorize_code'],
        );
        $CedShopeeLibrary = new CedShopeeLibrary;
        $api_url = $param['api_url'];
        $res = $CedShopeeLibrary->getToken($api_url, $data);
        if (isset($res['access_token']) && empty($res['error'])) {
            Configuration::updateValue('CEDSHOPEE_ACCESS_TOKEN', $res['access_token']);
            Configuration::updateValue('CEDSHOPEE_REFRESH_TOKEN', $res['refresh_token']);
            Configuration::updateValue('CEDSHOPEE_EXPIRE_IN', $res['expire_in']);
            $link = new LinkCore();
            $controller_link = $link->getAdminLink('AdminModules') . '&configure=cedshopee';
            $json = array(
                'success' => true,
                'message' => "Access token has fetched successfully",
                'controller_url' => $controller_link
            );
        } else {
            $json = array('success' => false, 'message' => $res['message']);
        }
        die(json_encode($json));
    }

    public function getProductSettingForm()
    {
        $this->context->smarty->assign(array(
            'CEDSHOPEE_PRICE_VARIANT_TYPE' => Configuration::get('CEDSHOPEE_PRICE_VARIANT_TYPE')
        ));
      
        $price_variation_html = $this->context->smarty
            ->fetch(_PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/configuration/price_variation.tpl');

        return array(
            'legend' => array(
                'title' => $this->l('Product Setting'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'col' => 6,
                    'type' => 'html',
                    'label' => $this->l('Price Variation'),
                    'name' => $price_variation_html,
                ),
                array(
                    'col' => 6,
                    'type' => 'text',
                    'id' => 'fixed_price',
                    'prefix' => '<i class="icon icon-money"></i>',
                    'desc' => $this->l('Enter the Fixed amount which is to be added 
                    in product default price while creating or updating product feed for Shopee.'),
                    'name' => 'CEDSHOPEE_PRICE_VARIANT_FIXED',
                    'label' => $this->l(' Fixed Amount'),
                ),
                array(
                    'col' => 6,
                    'type' => 'text',
                    'id' => 'fixed_per',
                    'prefix' => '<i class="icon icon-money"></i>',
                    'desc' => $this->l('Enter the Fixed percent which is to be added 
                    in product default price while creating or updating product feed for Shopee. 
                    Do not include any symbol like "%" etc.'),
                    'name' => 'CEDSHOPEE_PRICE_VARIANT_PER',
                    'label' => $this->l(' Fixed Percentage'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Update Stock/Price on Edit'),
                    'desc' => $this->l('Update product inventory & price at Shopee on product edit at store'),
                    'name' => 'CEDSHOPEE_UPDATE_INVENTORY_EDIT',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Update Product on Edit'),
                    'desc' => $this->l('Update product at Shopee on edit at store'),
                    'name' => 'CEDSHOPEE_UPDATE_PRODUCT_EDIT',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
            ),
        );
    }
    /*
    * Order form details
    */
    public function getOrderSettingForm()
    {
        $db = Db::getInstance();
        $id_lang = ((int)Configuration::get('CEDSHOPEE_LANGUAGE_STORE')) ?
            (int)Configuration::get('CEDSHOPEE_LANGUAGE_STORE')
            : (int)Configuration::get('PS_LANG_DEFAULT');

        $order_states = $db->ExecuteS("SELECT `id_order_state`,`name` 
       FROM `" . _DB_PREFIX_ . "order_state_lang` where `id_lang` = '" . (int)$id_lang . "'");

        $order_carriers = $db->ExecuteS("SELECT `id_carrier`,`name` 
        FROM `" . _DB_PREFIX_ . "carrier` where `active` = '1'");

        $payment_methods = array();

        $modules_list = Module::getPaymentModules();

        foreach ($modules_list as $module) {
            $module_obj = Module::getInstanceById($module['id_module']);
            if ($module_obj) {
                array_push(
                    $payment_methods,
                    array('id' => $module_obj->name, 'name' => $module_obj->displayName)
                );
            }
        }

        $shopee_status = array(
            array('id_marketplace_carrier' => '1', 'name' => 'UNPAID'),
            array('id_marketplace_carrier' => '2', 'name' => 'TO_CONFIRM_RECEIVE'),
            array('id_marketplace_carrier' => '3', 'name' => 'READY_TO_SHIP'),
            array('id_marketplace_carrier' => '4', 'name' => 'PROCESSED'),
            array('id_marketplace_carrier' => '5', 'name' => 'SHIPPED'),
            array('id_marketplace_carrier' => '6', 'name' => 'COMPLETED'),
            array('id_marketplace_carrier' => '7', 'name' => 'IN_CANCEL'),
            array('id_marketplace_carrier' => '8', 'name' => 'CANCELLED'),
            array('id_marketplace_carrier' => '9', 'name' => 'INVOICE_PENDING')


        );


        $this->context->smarty->assign(
            array(
                'order_carriers' => $order_states,
                // 'countries' => $order_states,
                'marketplace_carriers' => $shopee_status,
                'carrier_mappings' => Configuration::get('CEDSHOPEE_STATUS_MAPPING') ? json_decode(
                    Configuration::get('CEDSHOPEE_STATUS_MAPPING'),
                    true
                ) : array()
            )
        );

        $carrier_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/carrier_mapping.tpl'
        );
        return array(
            'legend' => array(
                'title' => $this->l('Order Setting'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(

                array(
                    'col' => 6,
                    'type' => 'text',
                    'prefix' => '<i class="icon icon-envelope"></i>',
                    'desc' => $this->l('Default Customer ID to create order on store which are imported form Shopee.'),
                    'name' => 'CEDSHOPEE_CUSTOMER_ID',
                    'label' => $this->l('Customer ID'),
                ),
                array(
                    'col' => 6,
                    'type' => 'text',
                    'required' => false,
                    'name' => 'CEDSHOPEE_ORDER_EMAIL',
                    'label' => $this->l('Order Email'),
                ),
                array(
                    'col' => 6,
                    'type' => 'datetime',
                    'prefix' => '<i class="icon icon-envelope"></i>',
                    'desc' => $this->l('Enter time of created  fetch order'),
                    'name' => 'CEDSHOPEE_ORDER_CREATED_FROM',
                    'label' => $this->l('Oder Created From'),
                ),
                array(
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Order status when Import Mapping'),
                    'name' => $carrier_html,
                ),
                 array(
                     'type' => 'select',
                     'col' => 6,
                     'label' => $this->l('Order status when Import'),
                     'desc' => $this->l('Order Status While importing order.'),
                     'name' => 'CEDSHOPEE_ORDER_STATE_IMPORT',
                     'required' => false,
                     'default_value' => '',
                     'options' => array(
                         'query' => $order_states,
                         'id' => 'id_order_state',
                         'name' => 'name',
                     )
                 ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Order status when cancelled at Shopee'),
                    'desc' => $this->l('Order Status after cancel order.'),
                    'name' => 'CEDSHOPEE_ORDER_STATE_CANCEL',
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Order status when Shipped'),
                    'desc' => $this->l('Order Status after order Shipped.'),
                    'name' => 'CEDSHOPEE_ORDER_STATE_SHIPPED',
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Order Payment'),
                    'desc' => $this->l('Payment method used to import order from Shopee.'),
                    'name' => 'CEDSHOPEE_ORDER_PAYMENT',
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => $payment_methods,
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Order Carrier'),
                    'desc' => $this->l('Order Carrier While importing order.'),
                    'name' => 'CEDSHOPEE_ORDER_CARRIER',
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => $order_carriers,
                        'id' => 'id_carrier',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Auto Reject Order'),
                    'name' => 'CEDSHOPEE_REJECTED_ORDER',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                ),
            ),
        );
    }

    /*
    * Order form details
    */
    public function getCronInfoForm()
    {
        $this->context->smarty->assign(array(
            'base_url' => Context::getContext()->shop->getBaseURL(true),
            'cron_secure_key' => Configuration::get('CEDSHOPEE_CRON_SECURE_KEY'),
            'CEDSHOPEE_CRON_SECURE_KEY' => Configuration::get('CEDSHOPEE_CRON_SECURE_KEY')
        ));
        $cron_html = $this->display(
            __FILE__,
            'views/templates/admin/configuration/cron_url.tpl'
        );

        return array(
            'legend' => array(
                'title' => $this->l('Cron Url'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'col' => 12,
                    'type'  => 'html',
                    'label'  => $this->l(''),
                    'name' => $cron_html,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if (Tools::getIsset($key)) {
                $value = Tools::getValue($key);
                $key = trim($key);
                if ($key == 'CEDSHOPEE_STATUS_MAPPING') {
                    $value = json_encode($value);
                }
                Configuration::updateValue($key, $value);
            }
        }
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $config_keys = array(
            'ENABLE',
            'DEBUG_MODE',

            'MODE',
            'REDIRECT_URI',
            'BUTTON',
            'LIVE_API_URL',
            'LIVE_PARTNER_ID',
            'LIVE_SHOP_ID',
            'LIVE_SIGNATURE',
            'SANDBOX_API_URL',
            'SANDBOX_PARTNER_ID',
            'SANDBOX_SHOP_ID',
            'SANDBOX_SIGNATURE',

            'STATE',
            'SCOPE',
            'BUTTON',
            'CODE',
            'ACCESS_TOKEN',

            'PRICE_VARIATION_TYPE',
            'PRICE_VARIANT_TYPE',
            'PRICE_VARIANT_FIXED',
            'PRICE_VARIANT_PER',
            'UPDATE_INVENTORY_EDIT',
            'UPDATE_PRODUCT_EDIT',
            'AUTO_DELETE_PRODUCT',

            'CUSTOMER_ID',
            'ORDER_EMAIL',
            'ORDER_CREATED_FROM',
            //            'ORDER_CREATED_TO',
            'REJECTED_ORDER',
            'FETCH_ORDER',
            'ORDER_STATE_IMPORT',
            //            'ORDER_STATE_ACKNOWLEDGE',
            'ORDER_STATE_CANCEL',
            'ORDER_STATE_SHIPPED',
            'ORDER_CARRIER',
            'ORDER_PAYMENT',
            'STATUS_MAPPING',

            'CRON_SECURE_KEY'
        );

        $configValues = array();
        foreach ($config_keys as $config_key) {
            $configValues['CEDSHOPEE_' . $config_key] = Configuration::get('CEDSHOPEE_' . $config_key) ?
                Configuration::get('CEDSHOPEE_' . $config_key) : '';
        }

        return $configValues;
    }

    public function ajaxProcessGenerateToken()
    {
        $json = array('success' => false, 'message' => '');
        $post = Tools::getAllValues();

        if (!empty($post['shop_signature'])) {
            $merge_key = $post['partner_id'] . '/api/v2/shop/auth_partner' . $post['timestamp'];
            $token = hash_hmac('sha256', $merge_key, $post['shop_signature']);
            $json = array('success' => true, 'message' => $token);
        }

        die(json_encode($json));
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!Module::isEnabled($this->name)) {
            return false;
        }
        if (method_exists($this->context->controller, 'addCSS')) {
            $this->context->controller->addCSS($this->_path . 'views/css/tab.css');
        }
    }

    public function hookActionProductUpdate($params)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;

        try {
            $idProduct = isset($params['id_product']) ? $params['id_product'] : null;
            if (!empty($idProduct)) {
                if (Configuration::get('CEDSHOPEE_ENABLE') &&
                    Configuration::get('CEDSHOPEE_UPDATE_PRODUCT_EDIT')
                ) {
                    $response = $CedShopeeProduct->uploadProducts(array($idProduct));

                    $CedShopeeLibrary->log(
                        __METHOD__,
                        'Response',
                        'Hook Product Update ' . $idProduct,
                        json_encode($response),
                        true
                    );
                }
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Exception while product update hook',
                json_encode(
                    array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )
                ),
                true
            );
        }
    }

    public function hookActionUpdateQuantity($params)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;
        try {
            $idProduct = isset($params['id_product']) ? $params['id_product'] : null;
            $CedShopeeLibrary->log(
                __METHOD__,
                'itemstock test',
                'Update Inventory Response for product - ' . $idProduct,
                json_encode($params),
                true
            );
            if (!empty($idProduct)) {
                if (Configuration::get('CEDSHOPEE_ENABLE') &&
                    Configuration::get('CEDSHOPEE_UPDATE_INVENTORY_EDIT')
                ) {
                    $response = $CedShopeeProduct->updateInventory($idProduct);
                    $CedShopeeLibrary->log(
                        __METHOD__,
                        'Response',
                        'Hook Product Quantity Update ' . $idProduct,
                        json_encode(array('success' => true, 'message' => $response)),
                        true
                    );

                    $result = $CedShopeeProduct->updatePrice($idProduct);
                    $CedShopeeLibrary->log(
                        __METHOD__,
                        'Response',
                        'Hook Product Price Update ' . $idProduct,
                        json_encode(array('success' => true, 'message' => $result)),
                        true
                    );
                }
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Exception while product update quantity hook',
                json_encode(
                    array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )
                ),
                true
            );
        }
    }

    public function hookActionProductDelete($params)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;
        try {
            $idProduct = isset($params['id_product']) ? $params['id_product'] : null;

            if (!empty($idProduct)) {
                if (Configuration::get('CEDSHOPEE_ENABLE') &&
                    Configuration::get('CEDSHOPEE_AUTO_DELETE_PRODUCT')
                ) {
                    $shopee_item_id = $CedShopeeProduct->getShopeeItemId($idProduct);
                    $shopee_item_id = isset($shopee_item_id) ? $shopee_item_id : '0';
                    if (!empty($shopee_item_id)) {
                        $requestSent = $CedShopeeLibrary->postRequest(
                            'product/delete_item',
                            array('item_id' => (int)$shopee_item_id)
                        );
                        if (empty($requestSent['response'] && empty($requestSent['error']))) {
                            $this->db->execute("DELETE FROM " . _DB_PREFIX_ .
                                "cedshopee_uploaded_products WHERE product_id =" . (int)$idProduct);
                            $this->db->execute("DELETE FROM " . _DB_PREFIX_ .
                                "cedshopee_product_variations WHERE product_id =" . (int)$idProduct);
                        }
                        $CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Hook Product Delete ' . $idProduct,
                            json_encode(array('success' => true, 'message' => $requestSent)),
                            true
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Exception while product delete hook',
                json_encode(
                    array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )
                ),
                true
            );
        }
    }

    public function hookActionOrderStatusPostUpdate($ps_order_data)
    {


        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeOrder = new CedShopeeOrder();
        try {
            $ps_order_data = (array)$ps_order_data;
            $id_order = $ps_order_data['id_order'];
            $db = Db::getInstance();
            if (Configuration::get('CEDSHOPEE_ENABLE')
            ) {
                $shopee_order_id = 0;
                $shopee_order_id = $db->getValue("SELECT `shopee_order_id` FROM `" . _DB_PREFIX_ .
                    "cedshopee_order` WHERE `prestashop_order_id` = " . $id_order);


                if ($shopee_order_id) {
                    $orderObject = new Order($id_order);
                    $data = (array)$orderObject;

                    $current_order_status = (int)$data['current_state'];
                    if ((int)$current_order_status) {
                        $order_state_when_shipped = (int)Configuration::get('CEDSHOPEE_ORDER_STATE_SHIPPED');

                        $order_state_when_cancel = (int)Configuration::get('CEDSHOPEE_ORDER_STATE_CANCEL');
                        if ($current_order_status == $order_state_when_shipped) {
                            // Shipping Provider
                            $params = array();
                            $id_carrier = (int)$data['id_carrier'];
                            $params['provider'] = '';
                            if ($id_carrier) {
                                $carrier_data = (array)new Carrier($id_carrier);
                                if (isset($carrier_data['name']) && $carrier_data['name']) {
                                    $params['carrier_name'] = $carrier_data['name'];
                                }
                            }
                            // Tracking Number
                            $params['tracking_number'] = isset($data['shipping_number']) ?
                                (int)$data['shipping_number'] : '';
                            $id_order_carrier = $orderObject->getIdOrderCarrier();

                            if (!empty($id_order_carrier)) {
                                $order_track = $this->db->executeS("SELECT `tracking_number`,`id_carrier`
                                     FROM `" . _DB_PREFIX_ . "order_carrier` WHERE `id_order` = " . $id_order . "
                                     AND `id_order_carrier` =" . $id_order_carrier);

                                if (!empty($order_track)) {
                                    $params['tracking_number'] = $order_track[0]['tracking_number'];
                                }
                            }
                            $params['ordersn'] = $shopee_order_id;
                            $ship_res = $CedShopeeOrder->shipOrder($params);
                            $CedShopeeLibrary->log(
                                __METHOD__,
                                'Hook Ship Order Status',
                                'shopee order id ' . $shopee_order_id,
                                json_encode(array(
                                    'ps_order_status' => $current_order_status,
                                    'order_state_when_shipped' => $order_state_when_shipped,
                                    'response' => $ship_res
                                )),
                                true
                            );
                        }
                        if ($current_order_status == $order_state_when_cancel) {
                            $Sql = $db->executeS("SELECT `order_data`,`shopee_order_id` FROM " . _DB_PREFIX_ .
                                "cedshopee_order WHERE shopee_order_id='" . $shopee_order_id . "'");
                            if (isset($Sql)) {
                                $params = array(
                                    'order_sn' => $Sql[0]['shopee_order_id'],
                                    'cancel_reason' => 'OUT_OF_STOCK',
                                    'item_list' => array(
                                        'item_id' => $Sql[0]['order_data']['item_list']['item_id'],
                                        'model_id' => $Sql[0]['order_data']['item_list']['model_id']
                                    )
                                );
                                $CedShopeeOrder->cancelOrder($params);
                            }
                        }
                    } else {
                        $CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Auto shipOrder ' . $shopee_order_id,
                            json_encode(array(
                                'Params' => array(
                                    'prestashop_order_data' => $data,
                                ),
                                'Response' => 'No Order Status found
                               in Prestashop Order to Ship At ThaMarket'
                            )),
                            true
                        );
                        return false;
                    }
                }
            } else {
                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Auto shipOrder ' . $id_order,
                    'Auto ship setting is disabled ',
                    '',
                    true
                );
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Auto shipOrder ' . $shopee_order_id,
                json_encode(array(
                    'Params' => array(
                        'prestashop_order_data' => $params,
                        'path' => $e->getMessage(),
                    ),
                    'Response' => $e->getMessage()
                )),
                true
            );
            return;
        }
    }
}
