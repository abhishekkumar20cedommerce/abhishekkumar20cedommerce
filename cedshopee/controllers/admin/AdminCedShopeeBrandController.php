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
 * @package   CedShopee
 */

include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';
include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProfile.php';

class AdminCedShopeeBrandController extends ModuleAdminController
{
    public $linioApiHelper;
    public $CedLinioAccount;
    public $db;
    public $profile_array;

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->profile_array = array();
        $this->bootstrap = true;
        $this->table = 'cedshopee_brand_mapping';
        $this->identifier = 'id';
        $this->addRowAction('edit');
        $this->addRowAction('remove');
        $dbp = Db::getInstance();
        $sql = 'SELECT `id`,`title` FROM `' . _DB_PREFIX_ . 'cedshopee_profile`';
        $res = $dbp->executeS($sql);
        if (is_array($res) & count($res) > 0) {
            foreach ($res as $r) {
                $this->profile_array[$r['id']] = $r['title'];
            }
        }
        $this->fields_list = array(
            'id' => array(
                'title' => 'ID',
                'type' => 'text',
            ),
            'store_brand' => array(
                'title' => 'Store Brand',
                'type' => 'text',
            ),
            'shopee_brand_name' => array(
                'title' => 'Shopee Brand',
                'type' => 'text',
            ),
            'profile_id' => array(
                'title' => ('Profile'),
                'align' => 'text-center',
                'filter_key' => 'profile_id',
                'type' => 'select',
                'list' => $this->profile_array,
                'filter_type' => 'int',
                'callback' => 'fyndiqProfileFilter'
            ),
        );
        if (Tools::getIsset('created') && Tools::getValue('created')) {
            $this->confirmations[] = "Brand created successfully";
        }
        if (Tools::getIsset('updated') && Tools::getValue('updated')) {
            $this->confirmations[] = "Brand updated successfully";
        }
        if (Tools::getIsset('removethemarket_brand')
            && Tools::getValue('removethemarket_brand')
            && Tools::getValue('id')) {
            $status = $this->deleteBrandMap(Tools::getValue('id'));
            if ($status) {
                $this->confirmations[] = 'Brand Deleted Successfully.';
            } else {
                $this->errors[] = 'Failed To Delete Brand(s).';
            }
        }
        if (Tools::getIsset('mapped') && Tools::getValue('mapped')) {
            $this->confirmations[] = 'Mapping Saved Successfully.';
        }
        parent::__construct();
    }

    public function fyndiqProfileFilter($data)
    {
        $dbp = Db::getInstance();
        $sql = 'SELECT `title` FROM `' . _DB_PREFIX_ . 'cedshopee_profile` WHERE id = '. $data;
        $res = $dbp->getValue($sql);

        if ($res) {
            $this->profile_array['profile_id'] = $res;
        }
        if (isset($this->profile_array[$data])) {
            return $this->profile_array[$data];
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_mapping'] = array(
                'href' => self::$currentIndex . '&addcedshopee_brand_mapping&token=' . $this->token,
                'desc' => 'Add Brand Mapping',
                'icon' => 'process-icon-new'
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

    public function displayRemoveLink($token = null, $id = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        if (!array_key_exists('Remove', self::$cache_lang)) {
            self::$cache_lang['Remove'] = 'Remove';
        }
        $tpl->assign(array(
            'href' => self::$currentIndex.'&'.$this->identifier.'='.
                $id.'&removethemarket_brand='.
                $id.'&token='.($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Remove'],
            'id' => $id
        ));
        return $tpl->fetch();
    }

    public function deleteBrandMap($id)
    {
        $result = $this->db->delete(
            'cedshopee_brand_mapping',
            'id='.(int)$id
        );
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function renderForm()
    {
        $already_mapped_brands = array();
        $store_brands = array();
        $manufacturers = Manufacturer::getManufacturers();
        $profile = $this->db->executeS("SELECT `id`,`title`,`shopee_category` FROM " ._DB_PREFIX_ .
            "cedshopee_profile");
        if (!empty($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                if (!isset($store_brands[$manufacturer['id_manufacturer']])) {
                    $store_brands[$manufacturer['id_manufacturer']] = array(
                        'id' => $manufacturer['id_manufacturer'],
                        'name' => $manufacturer['name']
                    );
                }
            }
        }
        $themarket_brands = array();
        $brands = array();
        if (Tools::getIsset('id') && Tools::getValue('id')) {
            $already_mapped_brands = $this->getAlreadyMapBrand(Tools::getValue('id'));
            if (count($already_mapped_brands)) {
                $category_ID = $this->db->getValue("SELECT `shopee_category` FROM `"._DB_PREFIX_.
                    "cedshopee_profile` WHERE `id` = '". (int) $already_mapped_brands['profile_id'] ."' ");
                $categories_brand = $this->db->executeS("SELECT `brands` FROM `"._DB_PREFIX_.
                    "cedshopee_brandlist` WHERE `category_id` = '". (int) $category_ID ."' ");
                $brands = json_decode($categories_brand[0]['brands'], true);
            }
        }
        $controllerUrl =  $this->context->link->getAdminLink('AdminCedShopeeBrand');
        $this->context->smarty->assign(
            array(
                'token' => $this->token,
                'controllerUrl' => $controllerUrl,
                'store_brands' => $store_brands,
                'themarket_brands' => $themarket_brands,
                'brand_row' => $brands,
                'profiles' => $profile,
                'already_mapped_brands' => $already_mapped_brands
            )
        );
        $return = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ .'cedshopee/views/templates/admin/brand/brand_mapping.tpl'
        );
        parent::renderForm();
        return $return;
    }

    public function postProcess()
    {
        try {
            if (Tools::getIsset('savebrandmapping') && Tools::getValue('savebrandmapping')) {
                $values = Tools::getAllValues();
                $status = $this->saveBrandMapping($values);
                if ($status) {
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedShopeeBrand') . '&created=1';
                    Tools::redirectAdmin($controller_link);
                    $this->confirmations[] = 'Brand(s) Mapped Successfully.';
                } else {
                    $this->errors[] = 'Failed To Map Brand(s).';
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        parent::postProcess();
    }

    public function saveBrandMapping($data)
    {
        if (isset($data['store_brand_id']) && !empty($data['store_brand_id'])
            && isset($data['cedshopee_brand_id']) &&  isset($data['cedshopee_brand_id'])) {
            $brand_exist = $this->db->getValue("SELECT `id` FROM `"._DB_PREFIX_."cedshopee_brand_mapping` WHERE 
            `store_brand_id` = '". (int) $data['store_brand_id'] ."' AND profile_id='".$data['profileid']."'");
            $store_brand = Manufacturer::getNameById($data['store_brand_id']);
            $shopee_brand = $this->getShopeeBrandNameById($data['cedshopee_brand_id'], $data['profileid']);
            if ($brand_exist) {
                $res = $this->db->update(
                    'cedshopee_brand_mapping',
                    array(
                        'store_brand_id' => (int)$data['store_brand_id'],
                        'store_brand' => pSQL($store_brand),
                        'shopee_brand_id' => (int) $data['cedshopee_brand_id'],
                        'shopee_brand_name' => pSQL($shopee_brand),
                        'profile_id' => $data['profileid']
                    ),
                    'id='.(int) $brand_exist
                );
            } else {
                $res = $this->db->insert(
                    'cedshopee_brand_mapping',
                    array(
                        'store_brand_id' => (int)$data['store_brand_id'],
                        'store_brand' => pSQL($store_brand),
                        'shopee_brand_id' => (int) $data['cedshopee_brand_id'],
                        'shopee_brand_name' => pSQL($shopee_brand),
                        'profile_id' => $data['profileid']
                    )
                );
            }
            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getAlreadyMapBrand($id)
    {
        $result = $this->db->ExecuteS("SELECT * FROM `"._DB_PREFIX_.
            "cedshopee_brand_mapping` WHERE `id`= '". (int) $id ."' ");
        if (is_array($result) && count($result)) {
            return $result['0'];
        } else {
            return array();
        }
    }

    public function getThemarketBrands()
    {
        $brands = $this->db->executeS("SELECT * FROM `"._DB_PREFIX_."ced_themarket_brand` ");
        return $brands;
    }

    public function ajaxProcessGetBrandOption()
    {
        $prfileId = Tools::getValue("profile_id");
        $profile = new CedShopeeProfile();
        $category_ID = $this->db->getValue("SELECT `shopee_category` FROM `"._DB_PREFIX_."cedshopee_profile` WHERE 
        `id` = '". (int) $prfileId ."' ");
        $profile->getBrandList($category_ID);

        $categories_brand = $this->db->executeS("SELECT `brands` FROM `"._DB_PREFIX_."cedshopee_brandlist` WHERE
        `category_id` = '". (int) $category_ID ."' ");
        if (isset($categories_brand) && isset($categories_brand[0]['brands'])) {
            die($categories_brand[0]['brands']);
        } else {
            die();
        }
    }

    public function getShopeeBrandNameById($linio_brand_id, $profileId)
    {
        $category_ID = $this->db->getValue("SELECT `shopee_category` FROM `"._DB_PREFIX_."cedshopee_profile` WHERE 
        `id` = '". (int) $profileId ."' ");
        $categories_brand = $this->db->executeS("SELECT `brands` FROM `"._DB_PREFIX_."cedshopee_brandlist` WHERE 
        `category_id` = '". (int) $category_ID ."' ");
        $brands = json_decode($categories_brand[0]['brands'], true);
        foreach ($brands as $value) {
            if ($value['brand_id'] == $linio_brand_id) {
                return $value['original_brand_name'];
            }
        }
        return false;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJquery();
    }
}
