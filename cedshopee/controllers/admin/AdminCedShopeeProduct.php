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

require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';
require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProduct.php';

class AdminCedShopeeProductController extends ModuleAdminController
{
    /**
     * @var string name of the tab to display
     */
    protected $tab_display;
    protected $object;
    protected $product_attributes;
    protected $position_identifier = 'id_product';
    protected $submitted_tabs;
    protected $id_current_category;
    protected $profile_array;
    
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = false;
        $this->list_no_link = true;
        $this->explicitSelect = true;
        $this->profile_array = array();
        $this->addRowAction('edit');
        $this->addRowAction('upload');
        $this->addRowAction('quantity');
        $this->addRowAction('prices');
        $this->addRowAction('remove');
        $this->bulk_actions = array(
            'upload' => array(
                'text' => ('Upload selected'),
                'icon' => 'icon-upload',
            ),
            'updateStock' => array(
                'text' => ('Update Quantity'),
                'icon' => 'icon-refresh',
            ),
            'updatePrice' => array(
                'text' => ('Update Price'),
                'icon' => 'icon-refresh',
            ),
            'remove' => array(
                'text' => ('Remove From Shopee'),
                'icon' => 'icon-trash',
            )
        );
        if (!Tools::getValue('id_product')) {
            $this->multishop_context_group = false;
        }
        $sql = 'SELECT `id`,`title` FROM `' . _DB_PREFIX_ . 'cedshopee_profile`';
        $res = $this->db->executeS($sql);
        if (is_array($res) & count($res) > 0) {
            foreach ($res as $r) {
                $this->profile_array[$r['id']] = $r['title'];
            }
        }
        parent::__construct();
        /* Join categories table */
        if ($id_category = (int)Tools::getValue('productFilter_cl!name')) {
            $this->_category = new Category((int)$id_category);
            $_POST['productFilter_cl!name'] = $this->_category->name[$this->context->language->id];
        } else {
            if ($id_category = (int)Tools::getValue('id_category')) {
                $this->id_current_category = $id_category;
                $this->context->cookie->id_category_products_filter = $id_category;
            } elseif ($id_category = $this->context->cookie->id_category_products_filter) {
                $this->id_current_category = $id_category;
            }

            if ($this->id_current_category) {
                $this->_category = new Category((int)$this->id_current_category);
            } else {
                $this->_category = new Category();
            }
        }
        $this->_join .= '
        LEFT JOIN `'._DB_PREFIX_.'stock_available` sav ON (sav.`id_product` = a.`id_product` 
        AND sav.`id_product_attribute` = 0
        '.StockAvailable::addSqlShopRestriction(null, null, 'sav').') ';

        $alias = 'sa';
        $alias_image = 'image_shop';

        $id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP ?
            (int)$this->context->shop->id : 'a.id_shop_default';

        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_shop` sa ON (a.`id_product` = sa.`id_product` 
                AND sa.id_shop = ' . $id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` b 
                ON (a.`id_product` = b.id_product AND b.id_shop = ' . $id_shop . ' 
                AND b.`id_lang`="' . (int)$this->context->language->id . '")
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl 
                ON (' . $alias . '.`id_category_default` = cl.`id_category` 
                AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . $id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . $id_shop . ')
                
                LEFT JOIN `' . _DB_PREFIX_ . 'cedshopee_profile_products` cbprofile 
                ON (a.`id_product` = cbprofile.`product_id`)
                LEFT JOIN `' . _DB_PREFIX_ . 'cedshopee_profile` cbp 
                ON (cbp.`id` = cbprofile.`shopee_profile_id`)
                LEFT JOIN `' . _DB_PREFIX_ . 'cedshopee_uploaded_products` cbprod 
                ON (cbprod.`product_id` = b.`id_product`)
                
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop 
                ON (image_shop.`id_product` = a.`id_product` 
                AND image_shop.`cover` = 1 AND image_shop.id_shop = ' . $id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = image_shop.`id_image`)
                LEFT JOIN `' . _DB_PREFIX_ . 'product_download` pd 
                ON (pd.`id_product` = a.`id_product` AND pd.`active` = 1)';

        $this->_select .= 'shop.`name` AS `shopname`, a.`id_shop_default`, ';
        $this->_select .= 'cbp.`id` AS `id_shopee_profile`, ';
        $this->_select .= 'cbprod.`error_message` AS `error_message`, ';
        $this->_select .= 'cbprod.`shopee_status` AS `shopee_status`, ';
        $this->_select .= 'cbprod.`shopee_item_id` AS `shopee_item_id`, ';

        $this->_select .= $alias_image . '.`id_image` AS `id_image`, a.`id_product` as `id_temp`,
            cl.`name` AS `name_category`, '
            . $alias . '.`price` AS `price_final`, a.`is_virtual`, pd.`nb_downloadable`, 
            sav.`quantity` AS `sav_quantity`, '
            . $alias . '.`active`, IF(sav.`quantity`<=0, 1, 0) AS `badge_danger`';

        $this->_group = 'GROUP BY ' . $alias . '.id_product';

        $this->_use_found_rows = true;

        $this->fields_list = array();
        $this->fields_list['id_product'] = array(
            'title' => ('ID'),
            'align' => 'center',
            'class' => 'fixed-width-xs',
            'type' => 'int'
        );
        $this->fields_list['image'] = array(
            'title' => ('Image'),
            'align' => 'center',
            'image' => 'p',
            'orderby' => false,
            'filter' => false,
            'search' => false
        );
        $this->fields_list['name'] = array(
            'title' => ('Name'),
            'filter_key' => 'b!name',
            'class' => 'fixed-width-sm',
        );
        $this->fields_list['reference'] = array(
            'title' => ('Reference'),
            'align' => 'left',
        );
        $this->fields_list['price'] = array(
            'title' => ('Base price'),
            'type' => 'price',
            'align' => 'text-right',
            'filter_key' => 'a!price'
        );
        $this->fields_list['price_final'] = array(
            'title' => ('Final price'),
            'type' => 'price',
            'align' => 'text-right',
            'havingFilter' => true,
            'orderby' => false,
            'search' => false
        );
       
        if (Configuration::get('PS_STOCK_MANAGEMENT')) {
            $this->fields_list['sav_quantity'] = array(
                'title' => ('Quantity'),
                'type' => 'int',
                'align' => 'text-right',
                'filter_key' => 'sav!quantity',
                'orderby' => true,
                'badge_danger' => true,
                'hint' => ('This is the quantity available in the current shop/group.'),
            );
        }

        $this->fields_list['id_shopee_profile'] = array(
            'title' => ('Profile'),
            'type' => 'select',
            'align' => 'text-center',
            'filter_key' => 'cbp!id',
            'list' => $this->profile_array,
            'filter_type' => 'int',
            'callback' => 'shopeeProfileFilter'
        );

        $this->fields_list['active'] = array(
            'title' => ('Status'),
            'active' => 'status',
            'filter_key' => $alias.'!active',
            'align' => 'text-center',
            'type' => 'bool',
            'class' => 'fixed-width-sm',
            'orderby' => false
        );
        
        $this->fields_list['shopee_status'] = array(
            'title' => ('Shopee Status'),
            'type' => 'text',
            'align' => 'text-right',
            'havingFilter' => true,
            'orderby' => true,
            'class' => 'fixed-width-xs',
            'search' => true

        );
        $this->fields_list['shopee_item_id'] = array(
            'title' => ('Shopee Item ID'),
            'type' => 'text',
            'align' => 'text-right',
            'havingFilter' => true,
            'orderby' => true,
            'class' => 'fixed-width-xs',
            'search' => true
        );

        $this->fields_list['error_message'] = array(
            'title' => ('View Details'),
            'align' =>'text-left',
            'search' => false,
            'class' => 'fixed-width-xs',
            'callback' => 'viewDetailsButton',
        );
    }

    public function shopeeProfileFilter($data)
    {
        if (isset($this->profile_array[$data])) {
            return $this->profile_array[$data];
        }
    }

    public function viewDetailsButton($data, $rowData)
    {
        $productID = isset($rowData['id_product'])?$rowData['id_product']: '';
        $this->context->smarty->assign(
            array(
                'product_id' => $productID,
                'data' => $data,
                'token' => $this->token
            )
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ .'cedshopee/views/templates/admin/product/product_validation_detail.tpl'
        );
    }

    public function ajaxProcessViewDetails()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $product_id = Tools::getValue('product_id');

        $json = array();
        if (!empty($product_id)) {
            $shopee_item_id = $this->db->getValue("SELECT `shopee_item_id` 
            FROM `". _DB_PREFIX_ ."cedshopee_uploaded_products` WHERE `product_id` = ". (int) $product_id);
            if (!empty($shopee_item_id)) {
                $url = 'item/get';
                $params = array('item_id' => (int) $shopee_item_id);
                $response = $CedShopeeLibrary->postRequest($url, $params);
                if (isset($response['item'])) {
                    $json = array('success' => true, 'message' => $response['item']);
                } elseif (isset($response['error'])) {
                    $json = array('success' => false, 'message' => $response['error']);
                } elseif (isset($response['msg'])) {
                    $json = array('success' => false, 'message' => $response['msg']);
                } else {
                    $json = array('success' => false, 'message' => 'Item Not Found On Shopee.');
                }
            } else {
                $json = array('success' => false, 'message' => 'Item Not Found On Shopee.');
            }
            die(json_encode($json));
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['upload_all'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedShopeeBulkUploadProduct'),
                'desc' => $this->l('Upload All', null, null, false),
                'icon' => 'process-icon-upload'
            );
            $this->page_header_toolbar_btn['fetchstatus'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedShopeeUpdateStatus'),
                'desc' => 'Update Status',
                'icon' => 'process-icon-download'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function displayUploadLink($token = null, $id = null)
    {
        if (!array_key_exists('Upload', self::$cache_lang)) {
            self::$cache_lang['Upload'] = 'Upload/Update';
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id
                . '&upload_update=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Upload'],
            'id' => $id
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/product/list/upload_row_action.tpl'
        );
    }

    public function displayQuantityLink($token = null, $id = null)
    {
        if (!array_key_exists('Quantity', self::$cache_lang)) {
            self::$cache_lang['Quantity'] = 'Update Quantity';
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id
                . '&update_quantity=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Quantity'],
            'id' => $id
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/product/list/quantity_row_action.tpl'
        );
    }

    public function displayPricesLink($token = null, $id = null)
    {
        if (!array_key_exists('Prices', self::$cache_lang)) {
            self::$cache_lang['Prices'] = 'Update Price';
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id
                . '&update_price=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Prices'],
            'id' => $id
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/product/list/prices_row_action.tpl'
        );
    }

    public function displayRemoveLink($token = null, $id = null)
    {
        if (!array_key_exists('Remove', self::$cache_lang)) {
            self::$cache_lang['Remove'] = 'Remove Product';
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id
                . '&removeproduct=' . $id . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Remove'],
            'id' => $id
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/product/list/remove_product_row_action.tpl'
        );
    }

    public function postProcess()
    {
        try {
            if (Tools::isSubmit('submitProductSave')) {
                $this->saveProduct();
            }
            // Bulk Action Upload/Update Product
            if (Tools::getIsset('submitBulkuploadproduct')) {
                if (Tools::getIsset('productBox') && count(Tools::getValue('productBox'))) {
                    $this->processBulkUpload(Tools::getValue('productBox'));
                } else {
                    $this->errors[] = Tools::displayError('Please Select Product');
                }
            }
            // Bulk Action Update Product Quantity
            if (Tools::getIsset('submitBulkupdateStockproduct')) {
                if (Tools::getIsset('productBox') && count(Tools::getValue('productBox'))) {
                    $this->processBulkUpdateStock(Tools::getValue('productBox'));
                } else {
                    $this->errors[] = Tools::displayError('Please Select Product to Update Stock');
                }
            }
            // Bulk Action Update Product Price
            if (Tools::getIsset('submitBulkupdatePriceproduct')) {
                if (Tools::getIsset('productBox') && count(Tools::getValue('productBox'))) {
                    $this->processBulkUpdatePrice(Tools::getValue('productBox'));
                } else {
                    $this->errors[] = Tools::displayError('Please Select Product to Update Price');
                }
            }
            // Bulk Action Remove Product
            if (Tools::getIsset('submitBulkremoveproduct')) {
                if (Tools::getIsset('productBox') && count(Tools::getValue('productBox'))) {
                    $this->processBulkRemove(Tools::getValue('productBox'));
                } else {
                    $this->errors[] = Tools::displayError('Please Select Product to Update Price');
                }
            }
            // Upload/Update Product
            if (Tools::getIsset('upload_update') && Tools::getValue('upload_update')) {
                $productId = Tools::getValue('upload_update');
                $this->processBulkUpload(array($productId));
            }
            // Update Product Quantity
            if (Tools::getIsset('update_quantity') && Tools::getValue('update_quantity')) {
                $productId = Tools::getValue('update_quantity');
                $this->processBulkUpdateStock(array($productId));
            }
            // Update Product Price
            if (Tools::getIsset('update_price') && Tools::getValue('update_price')) {
                $productId = Tools::getValue('update_price');
                $this->processBulkUpdatePrice(array($productId));
            }
            // Remove Product
            if (Tools::getIsset('removeproduct') && Tools::getValue('removeproduct')) {
                $productId = Tools::getValue('removeproduct');
                $this->processBulkRemove(array($productId));
            }
        } catch (PrestaShopException $e) {
            $this->errors[] = $e;
        }
        parent::postProcess();
    }

    public function renderForm()
    {
        $link = new LinkCore();
        $redirect = $link->getAdminLink('AdminCedShopeeProfile').'&addcedshopee_profile';
        Tools::redirectAdmin($redirect);
    }

    public function getProductById($product_id)
    {
        $response = array();
        $result = $this->db->executeS("SELECT * FROM `". _DB_PREFIX_ ."cedshopee_uploaded_products` 
        WHERE `product_id` = '". $product_id ."' ");
        if (!empty($result)) {
            $productData = $result[0];
            $response['logistics'] = json_decode($productData['logistics'], true);
            $response['wholesale'] = json_decode($productData['wholesale'], true);
            return $response;
        } else {
            return array();
        }
    }

    public function saveProduct()
    {
        $product_id = Tools::getValue('id_product');
        $logistics = Tools::getValue('logistics');
        $wholesale = Tools::getValue('wholesale');
        if (!empty($product_id)) {
            $productExist = $this->db->getValue("SELECT `id` FROM `". _DB_PREFIX_ ."cedshopee_uploaded_products` 
            WHERE `product_id` = '". $product_id ."' ");
            if (!empty($productExist)) {
                $res = $this->db->update(
                    'cedshopee_uploaded_products',
                    array(
                        'logistics' => pSQL(json_encode($logistics)),
                        'wholesale' => pSQL(json_encode($wholesale))
                        ),
                    'id=' . (int)$productExist
                );
                if ($res) {
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedShopeeProduct').'&updated=1';
                    Tools::redirectAdmin($controller_link);
                    $this->confirmations[] = "Product data updated successfully";
                }
            } else {
                $res = $this->db->insert(
                    'cedshopee_uploaded_products',
                    array(
                        'product_id' => (int)$product_id,
                        'logistics' => pSQL(json_encode($logistics)),
                        'wholesale' => pSQL(json_encode($wholesale))
                        )
                );
                if ($res) {
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedShopeeProduct').'&created=1';
                    Tools::redirectAdmin($controller_link);
                    $this->confirmations[] = "Product data updated successfully";
                }
            }
        }
    }

    protected function processBulkUpload($product_ids = array())
    {
        if (is_array($product_ids) && count($product_ids)) {
            $CedShopeeProduct = new CedShopeeProduct;
            $result = $CedShopeeProduct->uploadProducts($product_ids);
            if (isset($result['success']) && $result['success']) {
                $this->confirmations[] = $result['success'];
            } else {
                $this->errors[] = $result['error'];
            }
        }
    }

    public function processBulkUpdateStock($product_ids = array())
    {
        $final_response = array();
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;
        try {
            if (is_array($product_ids) && count($product_ids)) {
                $updated = 0;
                $fail = 0;
                if (is_array($product_ids) && count($product_ids)) {
                    foreach ($product_ids as $product_id) {
                        $result = $CedShopeeProduct->updateInventory($product_id);

                        if (isset($result['item'])) {
                            $updated++;
                            $final_response['success'][] = $result['item'];
                        } elseif (isset($result['error'])) {
                            $fail++;
                            $final_response['error'][] = $result['error'];
                        }
                    }
                }
                if ($updated) {
                    if ($fail) {
                        $this->errors[] = implode("<br/>", $final_response['error']);
                    } else {
                        $this->confirmations[] = implode("<br/>", $final_response['success']);
                    }
                } elseif ($fail) {
                    $this->errors[] = implode("<br/>", $final_response['error']);
                } else {
                    $this->errors[] = 'Unable to update data.';
                }
            } else {
                $this->errors[] = 'Please Select Product to Update Inventory';
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                'AdminCedShopeeBulkUploadProductController::updateStock',
                'Exception',
                'Update Stock Exception',
                json_encode(array(
                    'status' => true,
                    'message' => $e->getMessage()
                )),
                true
            );
            die(json_encode(array(
                'status' => true,
                'message' => $e->getMessage()
            )));
        }
    }

    public function processBulkUpdatePrice($product_ids = array())
    {
        $final_response = array();
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;
        try {
            if (is_array($product_ids) && count($product_ids)) {
                $updated = 0;
                $fail = 0;
                if (is_array($product_ids) && count($product_ids)) {
                    foreach ($product_ids as $product_id) {
                        $result = $CedShopeeProduct->updatePrice($product_id);
                        if (isset($result['item'])) {
                            $updated++;
                            $final_response['success'][] = $result['item'];
                        } elseif (isset($result['error'])) {
                            $fail++;
                            $final_response['error'][] = $result['error'];
                        }
                    }
                }
                if ($updated) {
                    if ($fail) {
                        $this->errors[] = implode("<br/>", $final_response['error']);
                    } else {
                        $this->confirmations[] = implode("<br/>", $final_response['success']);
                    }
                } elseif ($fail) {
                    $this->errors[] = implode("<br/>", $final_response['error']);
                } else {
                    $this->errors[] = 'Unable to update data.';
                }
            } else {
                $this->errors[] = 'Please Select Product to Update Price';
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                'AdminCedShopeeBulkUploadProductController::updatePrice',
                'Exception',
                'Update Price Exception',
                json_encode(array(
                    'status' => true,
                    'message' => $e->getMessage()
                )),
                true
            );
            die(json_encode(array(
                'status' => true,
                'message' => $e->getMessage()
            )));
        }
    }

    public function processBulkRemove($product_ids = array())
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeProduct = new CedShopeeProduct;
        try {
            if (is_array($product_ids) && count($product_ids)) {
                foreach ($product_ids as $product_id) {
                    $shopee_item_id = $CedShopeeProduct->getShopeeItemId($product_id);
                    $shopee_item_id = isset($shopee_item_id) ? $shopee_item_id : '0';
                    if (!empty($shopee_item_id)) {
                        $requestSent = $CedShopeeLibrary->postRequest(
                            'product/delete_item',
                            array('item_id'=> (int)$shopee_item_id)
                        );
                        if (empty($requestSent['response'] && empty($requestSent['error']))) {
                            $this->db->execute("DELETE FROM "._DB_PREFIX_ .
                                "cedshopee_uploaded_products WHERE product_id =".(int) $product_id);
                            $this->db->execute("DELETE FROM "._DB_PREFIX_ .
                                "cedshopee_product_variations WHERE product_id =".(int) $product_id);
                            $this->confirmations[] = 'Product id '. $product_id.' Deleted Successfully';
                        } else {
                            $this->errors[] = $requestSent['error'];
                        }
                    } else {
                        $this->errors[] = 'Product Delete failed Item id not Found.';
                    }
                }
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                'AdminCedShopeeProductController::Remove',
                'Exception',
                'Product Remove Exception',
                json_encode(array(
                    'status' => true,
                    'message' => $e->getMessage()
                )),
                true
            );
            die(json_encode(array(
                'status' => true,
                'message' => $e->getMessage()
            )));
        }
    }
}
