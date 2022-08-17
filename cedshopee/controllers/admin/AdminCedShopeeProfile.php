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

include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';
include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProfile.php';

class AdminCedShopeeProfileController extends ModuleAdminController
{
    public function __construct()
    {
        $this->id_lang = Context::getContext()->language->id;
        $this->bootstrap = true;
        $this->table = 'cedshopee_profile';
        $this->className = 'CedShopeeProfile';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('edit');
        $this->addRowAction('deleteProfile');
        parent::__construct();
        $this->fields_list = array(
            'id' => array(
                'title' => 'ID',
                'type' => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'title' => array(
                'title' => 'Profile Name',
                'type' => 'text',
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'callback' => 'profileStatus',
                'orderby' => false
            ),
        );

        if (Tools::isSubmit('submitProfileSave')) {
            $this->saveProfile();
        }
        if (Tools::getIsset('created') && Tools::getValue('created')) {
            $this->confirmations[] = "Profile created successfully";
        }
        if (Tools::getIsset('updated') && Tools::getValue('updated')) {
            $this->confirmations[] = "Profile updated successfully";
        }
    }

    public function profileStatus($value)
    {
        $this->context->smarty->assign(array('status' => (string)$value));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/profile/profile_status.tpl'
        );
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_profile'] = array(
                'href' => self::$currentIndex . '&addcedshopee_profile&token=' . $this->token,
                'desc' => $this->l('Add New Profile', null, null, false),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['refresh_profile'] = array(
                'href' => self::$currentIndex . '&refresh_profile&token=' . $this->token,
                'desc' => $this->l('Refresh Profiles', null, null, false),
                'icon' => 'process-icon-refresh'
            );
        } elseif ($this->display == 'edit' || $this->display == 'add') {
            $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Back To List', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        if (Tools::getIsset('deleteprofile') && Tools::getValue('deleteprofile')) {
            $id = Tools::getValue('deleteprofile');
            $res = $this->deleteProfile($id);
            if ($res) {
                $this->confirmations[] = "Profile " . $id . " deleted successfully";
            } else {
                $this->errors[] = "Failed to delete Profile " . $id;
            }
        }
        // Refresh Profile
        if (Tools::getIsset('refresh_profile')) {
            $res = $this->addNewProfileProducts();
            if ($res) {
                $this->confirmations[] = "Profiles refreshed successfully";
            } else {
                $this->errors[] = "Failed to refresh Profiles ";
            }
        }
        parent::postProcess();
    }

    public function initContent()
    {
        if (Tools::getIsset('ajax') && Tools::getValue('ajax')) {
            $this->ajax = true;
        }
        parent::initContent();
    }

    public function displayDeleteProfileLink($token = null, $id = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');
        if (!array_key_exists('Delete', self::$cache_lang)) {
            self::$cache_lang['Delete'] = 'Delete';
        }
        $tpl->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&deleteprofile=' . $id .
                '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Delete'],
            'id' => $id
        ));
        return $tpl->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/profile/deleteprofile_row_action.tpl'
        );
    }

    public function renderForm()
    {
        $profileData = array();
        $general = array();
        $storeCategory = array();
        $profileAttributeMapping = array();
        $selectedLogistics = array();
        $selectedWholesale = array();
        $defaultMapping = array();
        $productManufacturer = array();
        $Languages = array();
        $Shops = array();
        $idProfile = '';
        $productManufacturer = Manufacturer::getManufacturers(
            false,
            0,
            true,
            false,
            false,
            false,
            true
        );
        $CedShopeeLibrary = new CedShopeeLibrary();
        $address_list = $CedShopeeLibrary->getShopeeAddressListOption();
        $Languages = Language::getLanguages(true, false, false);
        $Shops = Shop::getShops(true, false, false);
        $ShopeeAttributes = CedShopeeLibrary::getShopeeAttributes();
        $ShopeeDefaultValues = CedShopeeLibrary::getDefaultShopeeAttributes();
        $storeSystemValues = CedShopeeLibrary::getSystemAttributes();
        $logistics_list = CedShopeeLibrary::getLogistics();
        $shopeeCategories = CedShopeeLibrary::getShopeeCategories();
        $invoice_option = CedShopeeLibrary::getShopeeinvoiceOption();
        $vat_option = CedShopeeLibrary::getShopeeVatOption();
        $vat_origin = CedShopeeLibrary::getShopeevatorigin();
        $warranty_option = CedShopeeLibrary::getShopeewarrantyOption();
        $exclude_warranty = CedShopeeLibrary::getShopeewarrantyExcludeOption();

        $idProfile = Tools::getValue('id');
        $this->context->controller->addJqueryUi('ui.autocomplete');
        $this->context->controller->addCSS(
            _PS_MODULE_DIR_ . 'cedshopee/views/css/shopee_category_attribute.css'
        );

        if (!empty($idProfile)) {
            $cedShopeeProfile = new CedShopeeProfile();
            $profileData = $cedShopeeProfile->getProfileDataById($idProfile);
            //echo "<pre>"; print_r($profileData); die();
            $general = $profileData['general'];
            $storeCategory = $profileData['store_category'];
            $profileAttributeMapping = $profileData['profileAttributeMapping'];

            $selectedLogistics = $profileData['logistics'];
            $selectedWholesale = $profileData['wholesale'];
            $defaultMapping = $profileData['defaultMapping'];
            $tax = $profileData['profile_tax'];
            $complain_policy = $profileData['profile_complain_policy'];
        }
        $this->context->smarty->assign(array('profileId' => $idProfile));
        $this->context->smarty->assign(array(
            'controllerUrl' => $this->context->link->getAdminLink('AdminCedShopeeProfile'),
            'token' => $this->token,
            'ShopeeAttributes' => $ShopeeAttributes,
            'ShopeeDefaultValues' => $ShopeeDefaultValues,
            'storeSystemAttributes' => $storeSystemValues,
            'productManufacturer' => $productManufacturer,
            'Languages' => $Languages,
            'Shops' => $Shops,
            'invoice_types' => $invoice_option,
            'warranty_time' => $warranty_option,
            'vat_opt' => $vat_option,
            'origin' => $vat_origin,
            'logistics_list' => $logistics_list,
            'shopeeCategories' => $shopeeCategories,
            'exclude_ent_warranty' => $exclude_warranty,
            'address_list' => $address_list
        ));
        $this->context->smarty->assign(array(
            'general' => $general,
            'profileAttributeMapping' => $profileAttributeMapping,
            'logistics' => $selectedLogistics,
            'selectedWholesale' => $selectedWholesale,
            'defaultMapping' => $defaultMapping,
            'tax' => $tax,
            'complain' => $complain_policy,
        ));
//        echo "<pre>"; print_r($tax); die();
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $tree_categories_helper = new HelperTreeCategories('categories-treeview');
            $tree_categories_helper->setRootCategory((Shop::getContext() == Shop::CONTEXT_SHOP ?
                Category::getRootCategory()->id_category : 0))
                ->setUseCheckBox(true);
        } else {
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $root_category = Category::getRootCategory();
                $root_category = array(
                    'id_category' => $root_category->id_category,
                    'name' => $root_category->name
                );
            } else {
                $root_category = array('id_category' => '0', 'name' => $this->l('Root'));
            }
            $tree_categories_helper = new Helper();
        }
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $tree_categories_helper->setUseSearch(true);
            $tree_categories_helper->setSelectedCategories($storeCategory);
            $this->context->smarty->assign(array(
                'storeCategories' => $tree_categories_helper->render()
            ));
        } else {
            $this->context->smarty->assign(array(
                'storeCategories' => $tree_categories_helper->renderCategoryTree(
                    $root_category,
                    $storeCategory,
                    'categoryBox'
                )
            ));
        }
        $profileTemplate = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/profile/edit_profile.tpl'
        );
        parent::renderForm();
        return $profileTemplate;
    }

    public function saveProfile()
    {
        $db = Db::getInstance();

        $title = Tools::getValue('title');
        $storeCategory = Tools::getValue('categoryBox');
        $shopee_categories = Tools::getValue('shopee_category_id');
        $shopee_category = Tools::getValue('shopee_category_id');
        $tax_info = Tools::getValue('Profile_tax_info');
        $complain_rule = Tools::getValue('Profile_complain_policy');
        $profile_attribute_mapping = Tools::getValue('profile_attribute_mapping');

        if (isset($profile_attribute_mapping) && !empty($profile_attribute_mapping)) {
            foreach ($profile_attribute_mapping as $id_key => $attributes_value) {
                if (!empty($attributes_value['default_values']) ||
                    !empty($attributes_value['store_attribute']) ||
                    !empty($attributes_value['shopee_option'])
                ) {
                    $profile_attribute_mapping[$id_key]['shopee_attribute'] = $id_key;
                } else {
                    $profile_attribute_mapping[$id_key]['shopee_attribute'] = '';
                }
            }
        }

        $status = Tools::getValue('status');
        $logistics = Tools::getValue('logistics');
        $wholesale = Tools::getValue('wholesale');
        $default_mapping = Tools::getValue('defaultMapping');
        $profile_store = Tools::getValue('profile_store');
        $product_manufacturer = Tools::getValue('product_manufacturer');
        if (!Tools::getIsset('product_manufacturer')) {
            $product_manufacturer = array();
        }
        $profile_language = Tools::getValue('profile_language');
        $shopee_category_name = Tools::getValue('shopee_category');
        $profileId = Tools::getValue('id');
        if (empty(trim($default_mapping['name']))) {
            $this->errors[] = "please map name field";
        }
        if (empty(trim($default_mapping['description']))) {
            $this->errors[] = "please map description field";
        }
        if (empty(trim($default_mapping['price']))) {
            $this->errors[] = "please map price field";
        }
        if (empty(trim($default_mapping['quantity']))) {
            $this->errors[] = "please map quantity field";
        }
        if (empty(trim($default_mapping['reference']))) {
            $this->errors[] = "please map reference field";
        }
        if (empty(trim($default_mapping['weight']))) {
            $this->errors[] = "please map weight field";
        }
        if (empty(trim($title))) {
            $this->errors[] = "Tile is missing";
        }
        if (empty(trim($shopee_categories))) {
            $this->errors[] = "Missing shopee categories";
        }
        if (empty($storeCategory)) {
            $this->errors[] = "Missing profile categories";
        }

        if (empty($this->errors)) {
            try {
                if (!empty($profileId)) {
                    $res = $db->update(
                        'cedshopee_profile',
                        array(
                            'title' => pSQL($title),
                            'store_category' => pSQL(json_encode($storeCategory)),
                            'shopee_categories' => pSQL(json_encode($shopee_categories)),
                            'shopee_category' => pSQL($shopee_category),
                            'profile_attribute_mapping' => pSQL(json_encode($profile_attribute_mapping)),
                            'status' => (int)$status,
                            'logistics' => pSQL(json_encode($logistics)),
                            'wholesale' => pSQL(json_encode($wholesale)),
                            'default_mapping' => pSQL(json_encode($default_mapping)),
                            'profile_store' => pSQL(json_encode($profile_store)),
                            'product_manufacturer' => pSQL(json_encode($product_manufacturer)),
                            'profile_language' => (int)$profile_language,
                            'shopee_category_name' => pSQL($shopee_category_name),
                            'profile_tax_info' => pSQL(json_encode($tax_info)),
                            'profile_complain_policy' => pSQL(json_encode($complain_rule))
                        ),
                        'id=' . (int)$profileId
                    );
                    if ($res && count($storeCategory)) {
                        $prod_result = $this->updateProfileProducts($profileId, $storeCategory, 'update');
                        if ($prod_result) {
                            $link = new LinkCore();
                            $controller_link = $link->getAdminLink('AdminCedShopeeProfile') . '&updated=1';
                            Tools::redirectAdmin($controller_link);
                            $this->confirmations[] = "Profile updated successfully";
                        }
                    }
                } else {
                    $p_code = $db->getValue(
                        "SELECT `id` FROM `" . _DB_PREFIX_ . "cedshopee_profile` WHERE `title`='" .pSQL($title) ."'"
                    );
                    if (!$p_code) {
                        $res = $db->insert(
                            'cedshopee_profile',
                            array(
                                'title' => pSQL($title),
                                'store_category' => pSQL(json_encode($storeCategory)),
                                'shopee_categories' => pSQL(json_encode($shopee_categories)),
                                'shopee_category' => pSQL($shopee_category),
                                'profile_attribute_mapping' => pSQL(json_encode($profile_attribute_mapping)),
                                'status' => (int)$status,
                                'logistics' => pSQL(json_encode($logistics)),
                                'wholesale' => pSQL(json_encode($wholesale)),
                                'default_mapping' => pSQL(json_encode($default_mapping)),
                                'profile_store' => pSQL(json_encode($profile_store)),
                                'product_manufacturer' => pSQL(json_encode($product_manufacturer)),
                                'profile_language' => (int)$profile_language,
                                'shopee_category_name' => pSQL($shopee_category_name),
                                'profile_tax_info' => pSQL(json_encode($tax_info)),
                                'profile_complain_policy' => pSQL(json_encode($complain_rule))
                            )
                        );
                        $newProfileId = $db->Insert_ID();
                        if ($res && $newProfileId && count($storeCategory)) {
                            $prod_result = $this->updateProfileProducts($newProfileId, $storeCategory, 'new');
                            if ($prod_result) {
                                $link = new LinkCore();
                                $controller_link = $link->getAdminLink('AdminCedShopeeProfile') . '&created=1';
                                Tools::redirectAdmin($controller_link);
                                $this->confirmations[] = "Profile created successfully";
                            }
                        }
                    } else {
                        $this->errors[] = "Profile Title must be unique. " . $title .
                            " is already assigned to profile Id " . $p_code;
                    }
                }
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    public function updateProfileProducts($profileId, $categories = array(), $type = '')
    {
        if ($profileId && count($categories)) {
            $db = Db::getInstance();
            $res = '';
            $productIds = array();
            $sql = "SELECT DISTINCT cp.`id_product` FROM `" . _DB_PREFIX_ . "category_product` cp
            JOIN `" . _DB_PREFIX_ . "product` p ON (p.id_product = cp.`id_product`)
            WHERE `id_category` IN (" . implode(',', (array)$categories) . ")";
            $data = $db->executeS($sql);
            if (count($data)) {
                foreach ($data as $item) {
                    $productIds[] = $item['id_product'];
                }
            }

            $idsToDisable = array();
            if (count($productIds)) {
                $query = "SELECT `product_id` FROM `" . _DB_PREFIX_ . "cedshopee_profile_products` 
                WHERE `shopee_profile_id` != " . (int)$profileId . " AND `product_id` 
                IN (" . implode(',', (array)$productIds) . ")";
                $dbResult = $db->executeS($query);
                if (count($dbResult)) {
                    foreach ($dbResult as $re) {
                        $idsToDisable[] = $re['product_id'];
                    }
                }
                $query = "DELETE FROM `" . _DB_PREFIX_ . "cedshopee_profile_products` 
                WHERE `shopee_profile_id` != " . (int)$profileId . " AND `product_id` 
                IN (" . implode(',', (array)$productIds) . ")";
                $db->execute($query);
                if ($type == 'new') {
                } else {
                    $idsToDisableSameProfile = array();
                    $sqlQuery = "SELECT `product_id` FROM `" . _DB_PREFIX_ . "cedshopee_profile_products`
                     WHERE `shopee_profile_id` = " . (int)$profileId . " AND `product_id` 
                     NOT IN (" . implode(',', (array)$productIds) . ")";
                    $queryResult = $db->executeS($sqlQuery);
                    if (count($queryResult)) {
                        foreach ($queryResult as $res) {
                            $idsToDisableSameProfile[] = $res['product_id'];
                        }
                    }

                    $idsToDisable = array_merge($idsToDisable, $idsToDisableSameProfile);
                    $query = "DELETE FROM `" . _DB_PREFIX_ . "cedshopee_profile_products` 
                    WHERE `shopee_profile_id` = " . (int)$profileId . "";
                    $db->execute($query);
                }
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "cedshopee_profile_products` (shopee_profile_id,product_id) 
                VALUES";
                foreach ($productIds as $id) {
                    $sql .= "(" . (int)$profileId . ", " . (int)$id . "),";
                }
                $sql = rtrim($sql, ',');
                $sql .= ";";
                $res = $db->execute($sql);
                if ($res) {
                    return true;
                }
            }
        }
        return true;
    }

    public function addNewProfileProducts()
    {
        $db = Db::getInstance();

        $query = $db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_profile`");

        if (isset($query[0]) && !empty($query[0]) && is_array($query)) {
            foreach ($query as $profile_data) {
                $profileId = $profile_data['id'];
                $storeCategories = json_decode($profile_data['store_category'], true);
                $this->updateProfileProducts($profileId, $storeCategories, 'update');
            }
            return true;
        } else {
            return false;
        }
    }

    public function deleteProfile($id)
    {
        $db = Db::getInstance();
        if (!empty($id)) {
            $res = $db->delete(
                'cedshopee_profile_products',
                'shopee_profile_id=' . (int)$id
            );
            if ($res) {
                $res =  $db->delete(
                    'cedshopee_profile',
                    'id=' . (int)$id
                );
            }
            if ($res) {
                return true;
            }
        }
        return false;
    }

    /** converting MVC Attributes */
    public function ajaxProcessAttributesByCategory()
    {
        $message = '';
        $db = Db::getInstance();
        $result = Tools::getAllValues();
        $cedShopeeProfile = new cedShopeeProfile;
        $profile_id = Tools::getValue('id');
        $category_id = Tools::getIsset('category_id') ? Tools::getValue('category_id') : 0;
        $profile_id = Tools::getIsset('profile_id') ? Tools::getValue('profile_id') : 0;
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        if ($category_id) {
            $mapped_attributes_options = array();
            if ($profile_id) {
                $mapped_attributes_options = $cedShopeeProfile->getMappedAttributes($profile_id);
                //print_r($mapped_attributes_options); die();
                $MappedBrand = $db->executeS("SELECT `Brand` FROM `" . _DB_PREFIX_ .
                    "cedshopee_profile` WHERE `id` = '" . (int)$profile_id . "'");
            }
            $storeFeatures = Feature::getFeatures($default_lang, true);
            $store_options = $cedShopeeProfile->storeOptions();
            $product_fields = array();
            try {
                $columns = $db->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product`;");
                if (isset($columns) && count($columns)) {
                    $product_fields = $columns;
                }
                $this->arraySoryByColumn($product_fields, 'Field');
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }
            $storeAttributes = $store_options['options'];
            $attributes = $cedShopeeProfile->getAttributesByCategory($category_id);
        }

        if ($category_id) {
            $this->context->smarty->assign(
                array(
                    'model' => $attributes,
                    'product_field' => $product_fields,
                    'storeFeatures' => $storeFeatures,
                    'storeAttributes' => $storeAttributes,
                    'profileTechnicalDetails' => $mapped_attributes_options
                )
            );
            $technical_details = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ .
                'cedshopee/views/templates/admin/profile/profile_Shopee_mapping_details.tpl'
            );

            die(json_encode(array(
                'success' => true,
                'technical_details' => $technical_details,
            )));
        } else {
            die(json_encode(array(
                'success' => false,
                'message' => !empty($message) ? $message : 'Please Select Correct Leaf Category'
            )));
        }
    }

    public function getAttributeValueByID($storeAttributeId)
    {
        $returnResponse = array();
        $CedShopeeProfile = new CedShopeeProfile;
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        if (isset($storeAttributeId) &&  !empty($storeAttributeId)) {
            $attribute_group_id = $storeAttributeId;
            $type_array = explode('-', $attribute_group_id);
            if (isset($type_array['0']) && ($type_array['0'] == 'option')) {
                $returnResponse = $CedShopeeProfile->getStoreOptions(
                    '',
                    $type_array['1'],
                    ''
                );
            } elseif (isset($type_array['0']) && ($type_array['0'] == 'attribute')) {
                $option_value_query = FeatureValue::getFeatureValuesWithLang($default_lang, $type_array['1']);
                foreach ($option_value_query as $option_value) {
                    $returnResponse[] = array(
                        'option_value_id' => trim($option_value['id_feature_value']),
                        'name' => trim($option_value['value'])
                    );
                }
            }
        }
        return $returnResponse;
    }

    public function ajaxProcessGetStoreOptions()
    {
        $returnResponse = array();
        $CedShopeeProfile = new CedShopeeProfile;
        $data = Tools::getAllValues();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        if (isset($data['attribute_group_id']) &&
            !empty($data['attribute_group_id']) && isset($data['catId']) && !empty($data['catId'])
        ) {
            $attribute_group_id = $data['attribute_group_id'];
            $type_array = explode('-', $attribute_group_id);
            if (isset($type_array['0']) && ($type_array['0'] == 'option')) {
                $returnResponse = $CedShopeeProfile->getStoreOptions(
                    $data['catId'],
                    $type_array['1'],
                    $data['filter_name']
                );
            } elseif (isset($type_array['0']) && ($type_array['0'] == 'attribute')) {
                $option_value_query = FeatureValue::getFeatureValuesWithLang($default_lang, $type_array['1']);
                foreach ($option_value_query as $option_value) {
                    $returnResponse[] = array(
                        'option_value_id' => trim($option_value['id_feature_value']),
                        'name' => trim($option_value['value'])
                    );
                }
            }
        }
        die(json_encode($returnResponse));
    }

    public function arraySoryByColumn(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);
    }

    public function ajaxProcessAutocomplete()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $json = array();
        $request = Tools::getAllValues();
        if (isset($request) && !empty($request)) {
            $filter_name = Tools::getIsset('filter_name') ? Tools::getValue('filter_name') : '';
            $data = array('filter_name' => $filter_name);
            $results = $CedShopeeLibrary->getShopeeCategories($data);
            foreach ($results as $category) {
                $json[] = array(
                    'category_id' => $category['category_id'],
                    'name' => strip_tags(html_entity_decode(
                        $category['category_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    )),
                );
            }
        }
        die(json_encode($json));
    }

    public function ajaxProcessBrandAuto()
    {
        $CedShopeeProfile = new CedShopeeProfile;
        $returnResponse = array();
        $data = Tools::getAllValues();
        if (isset($data['filter_name']) && !empty($data['filter_name']) && isset($data['attribute_id']) &&
            !empty($data['attribute_id']) && isset($data['catId']) && !empty($data['catId'])
        ) {
            $attribute_id = $data['attribute_id'];
            $returnResponse = $CedShopeeProfile->getBrands($data['catId'], $attribute_id, $data['filter_name']);
        }
        die(json_encode($returnResponse));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJquery('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
    }
}
