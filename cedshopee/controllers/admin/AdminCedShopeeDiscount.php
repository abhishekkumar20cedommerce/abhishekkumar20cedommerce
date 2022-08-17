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

include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeDiscount.php';
include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';

class AdminCedShopeeDiscountController extends ModuleAdminController
{
    public function __construct()
    {
        $this->db         = Db::getInstance();
        $this->bootstrap  = true;
        $this->table      = 'cedshopee_discount';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('edit');

        $this->bulk_actions = array(
            'remove' => array(
                'text' => 'Delete Discount',
                'icon' => 'icon-trash'
            ),
        );

        $this->fields_list = array(
            'discount_id'       => array(
                'title' => 'Discount ID',
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'discount_name'     => array(
                'title' => 'Discount Name',
                'type'  => 'text',
            ),
            'start_date'     => array(
                'title' => 'Start Date',
                'type'  => 'datetime',
            ),
            'end_date'     => array(
                'title' => 'End Date',
                'type'  => 'datetime',
            ),
        );

        if (Tools::isSubmit('submitProfileSave')) {
            $this->saveDiscount();
        }
        if (Tools::getIsset('created') && Tools::getValue('created')) {
            $this->confirmations[] = "Discount added successfully";
        }
        if (Tools::getIsset('updated') && Tools::getValue('updated')) {
            $this->confirmations[] = "Discount updated successfully";
        }
        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_discount'] = array(
                'href' => self::$currentIndex . '&addcedshopee_discount&token=' . $this->token,
                'desc' => $this->l('Add Discount', null, null, false),
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

    public function renderForm()
    {
        $db = Db::getInstance();
        $CedShopeeDiscount = new CedShopeeDiscount();
        $shopeeItems = array();
        $this->context->controller->addJqueryUi('ui.autocomplete');

        $id = Tools::getValue('id');
        if (!empty($id)) {
            $discountData = $CedShopeeDiscount->getDiscountDataById($id);
            $discount = $discountData['discount'];
            $this->context->smarty->assign(array(
                'discount' => $discount
            ));
        }
        $items = Tools::getValue('shopee_item');

        if ($items) {
            foreach ($items as $item) {
                $sql = "SELECT cpp.`shopee_item_id`, pl.`name` FROM `"._DB_PREFIX_."cedshopee_uploaded_products` AS cpp 
                 LEFT JOIN `"._DB_PREFIX_."product_lang` AS pl ON(pl.id_product = cpp.product_id) 
                 WHERE cpp.`shopee_item_id` = '". (int) $item ."' ORDER BY pl.`name` ";
                $result = $db->executeS($sql);
                $shopeeItems[] = $result;
            }
        } elseif (!empty($id)) {
            $shopee_items = ($discount['items']) ? $discount['items'] : array();
            foreach ($shopee_items as $shopee_item) {
                $sql = "SELECT cpp.`shopee_item_id`, pl.`name` FROM `"._DB_PREFIX_."cedshopee_uploaded_products` AS cpp 
                 LEFT JOIN `"._DB_PREFIX_."product_lang` AS pl ON(pl.id_product = cpp.product_id) 
                 WHERE cpp.`shopee_item_id` = '". (int) $shopee_item ."' ORDER BY pl.`name` ";
                $result = $this->db->executeS($sql);
                $shopeeItems[] = $result;
            }
        } else {
            $shopeeItems = array();
        }

        $this->context->smarty->assign(array('id' => $id));
        $this->context->smarty->assign(array(
            'controllerUrl' => $this->context->link->getAdminLink('AdminCedShopeeDiscount'),
            'token' => $this->token,
            'shopeeItems' => $shopeeItems
        ));

        $discountTemplate = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/discount/edit_discount.tpl'
        );
        parent::renderForm();
        return $discountTemplate;
    }

    public function saveDiscount()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $request = Tools::getAllValues();

        $id = Tools::getValue('id');
        $discountID = Tools::getValue('discount_id');
        $discountName = Tools::getValue('discount_name');
        $startDate = Tools::getValue('start_date');
        $endDate = Tools::getValue('end_date');
        $items = Tools::getValue('shopee_items');
        $discount_data = array();

        if (empty(trim($discountName))) {
            $this->errors[] = "Missing discount name";
        }
        if (empty($startDate)) {
            $this->errors[] = "Missing start date";
        }
        if (empty($endDate)) {
            $this->errors[] = "Missing end date";
        } else {
            try {

                if (!empty($id)) {
                    $discount_data['start_time'] = strtotime($startDate);

                    $discount_data['end_time'] = strtotime($endDate);
                    $discount_data['discount_id'] = $discountID;
                    $discount_data['items'] = array();
                    foreach ($items as $shopee_item) {
                        $discount_data['items'][] = array('item_id' => (int)$shopee_item, 'item_promotion_price' => 90,
                            'purchase_limit' => 90);
                    }
                    $response = $CedShopeeLibrary->postRequest('discount/update_discount', $discount_data);
                    if (!Tools::getIsset($response['error'])) {
                        if (!empty($response['discount_id'])) {
                            $request['discount_id'] = $response['discount_id'];
                            $this->updateShopeeDiscount($request, $id);
                        }
                    } elseif (!empty($response['error'])) {
                        $this->errors[] = $response['error'];
                    } elseif (!empty($response['msg'])) {
                        $this->errors[] = $response['msg'];
                    } else {
                        $this->errors[] = 'No response from Shopee';
                    }
                } else {
                    $discount_data['discount_name'] = $discountName;
                    $discount_data['start_time'] = strtotime($startDate);
                    $discount_data['end_time'] = strtotime($endDate);
                    $discount_data['items'] = array();
                    foreach ($items as $shopee_item) {
                        $discount_data['items'][] = array('item_id' => (int)$shopee_item);
                    }
                    $response = $CedShopeeLibrary->postRequest('discount/add_discount', $discount_data);

                    if (!$response['error']) {
                        if (!empty($response['discount_id'])) {
                            $request['discount_id'] = $response['discount_id'];
                            $this->addShopeeDiscount($request);
                        }
                    } elseif (!empty($response['error'])) {

                        $this->errors[] = $response['error']. ', '. $response['message'];
                    } elseif (!empty($response['msg'])) {
                        $this->errors[] = $response['msg'];
                    } else {
                        $this->errors[] = 'No response from Shopee';
                    }
                }
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    public function addShopeeDiscount($request)
    {
        $res = $this->db->insert(
            'cedshopee_discount',
            array(
                'discount_id' => (int)$request['discount_id'],
                'discount_name' => pSQL($request['discount_name']),
                'start_date' => strtotime($request['start_date']),
                'end_date' => strtotime($request['end_date']),
                'items' => pSQL(json_encode($request['items']))
            )
        );
        if ($res) {
            $link = new LinkCore();
            $controller_link = $link->getAdminLink('AdminCedShopeeDiscount').'&created=1';
            Tools::redirectAdmin($controller_link);
            $this->confirmations[] = "Discount created successfully";
        }
    }

    public function updateShopeeDiscount($request, $id)
    {
        $res = $this->db->update(
            'cedshopee_discount',
            array(
                'discount_id' => (int)$request['discount_id'],
                'discount_name' => pSQL($request['discount_name']),
                'start_date' => strtotime($request['start_date']),
                'end_date' => strtotime($request['end_date']),
                'items' => pSQL(json_encode($request['items']))
            ),
            'id=' . (int)$id
        );
        if ($res) {
            $link = new LinkCore();
            $controller_link = $link->getAdminLink('AdminCedShopeeDiscount').'&updated=1';
            Tools::redirectAdmin($controller_link);
            $this->confirmations[] = "Discount" .$id. " updated successfully";
        }
    }

    public function processBulkRemove()
    {
        $CedShopeeLibrary = new CedShopeeLibrary();
        $discount_ids = Tools::getValue('cedshopee_discountBox');
        if (!empty($discount_ids)) {
            foreach ($discount_ids as $discount_id) {
                $discount_id_to_send = $this->db->getValue("SELECT `discount_id` 
                FROM " . _DB_PREFIX_ . "cedshopee_discount WHERE `id` = '". (int)$discount_id ."' ");
                $params = array(
                    'discount_id' => (int) $discount_id_to_send
                );
                $response = $CedShopeeLibrary->postRequest('discount/delete', $params);
                if (!Tools::getIsset($response['errors'])) {
                    if (!empty($response['discount_id'])) {
                        $this->db->Execute("DELETE FROM " . _DB_PREFIX_ . "cedshopee_discount 
                        WHERE `id` = '". (int)$discount_id ."' ");
                        $this->confirmations[] = 'Discount' . $discount_id . 'data deleted successfully!';
                    }
                } elseif (!empty($response['error'])) {
                    $this->errors[] = $response['error'];
                } elseif (!empty($response['msg'])) {
                    $this->errors[] = $response['msg'];
                } else {
                    $this->errors[] = 'No response from Shopee';
                }
            }
        } else {
            $this->errors[] = 'Please Select Discount';
        }
    }

    public function ajaxProcessAutocomplete()
    {
        $CedShopeeDiscount = new CedShopeeDiscount;
        $json = array();
        $request = Tools::getAllValues();

        if (isset($request) && !empty($request)) {
            $filter_name = Tools::getIsset('filter_name')?Tools::getValue('filter_name'):'';
            $data = array('filter_name' => $filter_name);
            $results = $CedShopeeDiscount->getShopeeItems($data);
            foreach ($results as $discount) {
                $json[] = array(
                    'shopee_item_id' => $discount['shopee_item_id'],
                    'name' => $discount['name'],
                );
            }
        }
        die(json_encode($json));
    }
}
