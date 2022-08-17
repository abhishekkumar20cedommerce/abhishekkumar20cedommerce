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

include_once  _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeOrder.php';
include_once  _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';

class AdminCedShopeeOrderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->list_no_link = true;
        $this->addRowAction('view');
        $this->bulk_actions = array(
            'cancel' => array(
                'text' => 'Cancel Order',
                'icon' => 'icon-refresh'
            ),
            'syncOrderStatus' => array(
                'text' => 'Sync Order Status',
                'icon' => 'icon-refresh'
            ),
        );

        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        parent::__construct();
        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        if ((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = a.id_customer
        AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
        country_lang.name as cname,
        IF (a.valid, 1, 0) badge_success';

        $this->_join = '
        LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
        JOIN `' . _DB_PREFIX_ . 'cedshopee_order` cwo ON (cwo.`prestashop_order_id` = a.`id_order`)
        LEFT JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery
        LEFT JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
        LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country`
        AND country_lang.`id_lang` = ' . (int)$this->context->language->id . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`
        AND osl.`id_lang` = ' . (int)$this->context->language->id . ')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $this->fields_list = array(
            'id_order' => array(
                'title' => 'ID',
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'shopee_order_id' => array(
                'title' => 'Purchase Order ID',
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'reference' => array(
                'title' => 'Reference'
            ),
            'customer' => array(
                'title' => 'Customer',
                'havingFilter' => true,
            ),
        );
        $this->fields_list = array_merge($this->fields_list, array(
            'total_paid_tax_incl' => array(
                'title' => $this->l('Total'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true
            ),
            'payment' => array(
                'title' => $this->l('Payment')
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            )
        ));
        $result = Db::getInstance()->ExecuteS('
        SELECT DISTINCT c.id_country, cl.`name`
        FROM `'._DB_PREFIX_.'orders` o
        '.Shop::addSqlAssociation('orders', 'o').'
        INNER JOIN `'._DB_PREFIX_.'address` a ON a.id_address = o.id_address_delivery
        INNER JOIN `'._DB_PREFIX_.'country` c ON a.id_country = c.id_country
        INNER JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` 
        AND cl.`id_lang` = '.(int)$this->context->language->id.')
        ORDER BY cl.name ASC');
        $country_array = array();
        foreach ($result as $row) {
            $country_array[$row['id_country']] = $row['name'];
        }
        $part1 = array_slice($this->fields_list, 0, 3);
        $part2 = array_slice($this->fields_list, 3);
        $part1['cname'] = array(
            'title' => $this->l('Delivery'),
            'type' => 'select',
            'list' => $country_array,
            'filter_key' => 'country!id_country',
            'filter_type' => 'int',
            'order_key' => 'cname'
        );
        $this->fields_list = array_merge($part1, $part2);
        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_ORDER;
        if (Tools::isSubmit('id_order')) {
            $order = new Order((int)Tools::getValue('id_order'));
            $this->context->cart = new Cart($order->id_cart);
            $this->context->customer = new Customer($order->id_customer);
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_order'] = array(
                'href' => self::$currentIndex . '&fetchorder&token=' . $this->token,
                'desc' => $this->l('Fetch Orders', null, null, false),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['upload_all'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedShopeeSyncOrderStatus'),
                'desc' => $this->l('Sync Orders Status', null, null, false),
                'icon' => 'process-icon-refresh'
            );
        } elseif ($this->display == 'view') {
            $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Back To List', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        if (Tools::getIsset('fetchorder')) {
            $CedShopeeOrder = new CedShopeeOrder();
            $response = $CedShopeeOrder->fetchOrder();
            if (isset($response['success']) && $response['success'] == true) {
                $this->confirmations[] = $response['message'];
            } else {
                $this->errors[] = isset($response['message']) ? $response['message'] : 'Failed to fetch Shopee orders';
            }
        }
        return parent::renderList();
    }

    public function renderForm()
    {
        $link = new LinkCore();
        $redirect = $link->getAdminLink('AdminOrders');
        Tools::redirectAdmin($redirect);
    }

    public function renderView()
    {
        $order = $this->loadObject();
        $order_data = (array)$order;
        $id_order = 0;
        if (isset($order_data['id']) && $order_data['id']) {
            $id_order = $order_data['id'];
        }
        if ($id_order) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_order` 
            WHERE `prestashop_order_id` = '" . (int)$id_order . "'";
            $result = $this->db->ExecuteS($sql);
            if (is_array($result) && count($result) && isset($result['0']['order_data'])) {
                $order_data = json_decode($result['0']['order_data'], true);
                if ($order_data) {
                    $imgUrl = Context::getContext()->shop->getBaseURL(true) .
                        'modules/cedshopee/views/img/loader.gif';
                    $this->context->smarty->assign(array(
                        'imgUrl' => $imgUrl,
                        'id_order' => $id_order,
                        'ordersn' => $order_data['order_sn'],
                        'order_status' => $order_data['order_status'],
                        'order_placed_date' => date('Y-m-d H:i:s', $order_data['create_time']),
                        'payment_method' => $order_data['payment_method'],
                        'shipping_carrier' => $order_data['shipping_carrier'],
                        'email' => $order_data['buyer_username'] . '@shopee.com',
                        //'tracking_no' => $order_data['tracking_no']
                    ));
                    if ($order_data['recipient_address']) {
                        $this->context->smarty->assign(array('customer_details' => $order_data['recipient_address']));
                    }

                    $subTotal = 0;
                    foreach ($order_data['item_list'] as $item) {
                        if (isset($item['model_discounted_price']) && !empty($item['model_discounted_price'])) {
                            $subTotal += $item['model_discounted_price'];
                        } elseif (isset($item['model_original_price']) &&
                            !empty($item['model_original_price'])
                        ) {
                            $subTotal += $item['model_original_price'];
                        }
                    }
                    if ($order_data['item_list']) {
                        $this->context->smarty->assign(array('items' => $order_data['item_list']));
                    }
                    $this->context->smarty->assign(array(
                        'shipping_fee' => $order_data['estimated_shipping_fee'],
                        //'escrow_amount' => $order_data['escrow_amount'],
                        'order_total' => $order_data['total_amount'],
                        'sub_total' => $subTotal,
                    ));
                }
                $this->context->smarty->assign('id_order', $id_order);
                $this->context->smarty->assign('token', $this->token);
                $this->context->smarty->assign(array('controllerUrl' =>
                $this->context->link->getAdminLink('AdminCedShopeeOrder')));
                $parent = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/order/form.tpl'
                );
                parent::renderView();

                return $parent;
            }
        }
    }

    public static function setOrderCurrency($echo, $tr)
    {
        $order = new Order($tr['id_order']);
        return Tools::displayPrice($echo, (int)$order->id_currency);
    }

    public function processBulkCancel()
    {
        $CedShopeeOrder = new CedShopeeOrder;
        $ids = Tools::getValue('orderBox');
        $db = Db::getInstance();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                if ($id) {
                    $Sql = $db->executeS("SELECT `order_data`,`shopee_order_id` FROM " . _DB_PREFIX_ .
                        "cedshopee_order WHERE id='" . (int)($id) . "'");
                    if (isset($Sql)) {
                        $params = array(
                            'order_sn' => $Sql[0]['shopee_order_id'],
                            'cancel_reason' => 'OUT_OF_STOCK',
                            'item_list' => array(
                                'item_id' => $Sql[0]['order_data']['item_list']['item_id'],
                                'model_id' => $Sql[0]['order_data']['item_list']['model_id']
                            )
                        );
                    }
                    $response = $CedShopeeOrder->cancelOrder($params);
                    if (isset($response['success']) && $response['success'] == true) {
                        $this->confirmations[] = isset($response['message']) ?
                            $response['message'] : "Order " . $Sql[0]['shopee_order_id'] . " cancelled successfully";
                    } else {
                        $this->errors[] = isset($response['message']) ?
                            $response['message'] : "Order " . $Sql[0]['shopee_order_id'] . " can not be cancelled";
                    }
                }
            }
        } else {
            $this->errors[] = Tools::displayError('Please Select Order');
        }
    }

    public function processBulkSyncOrderStatus()
    {
        $CedShopeeOrder = new CedShopeeOrder;
        $ids = Tools::getValue('orderBox');
        $db = Db::getInstance();
        if (!empty($ids)) {
            $order_to_fetch = array();
            foreach ($ids as $id) {
                $orders = $db->executeS("SELECT `shopee_order_id` FROM " . _DB_PREFIX_ .
                    "cedshopee_order WHERE prestashop_order_id='" . (int)($id) . "'");
                foreach ($orders as $order) {
                    $shopeeOrderId = $order['shopee_order_id'];
                    $order_to_fetch[] = $shopeeOrderId;
                }
            }
            $chunks = array_chunk($order_to_fetch, 50);
            $response = $CedShopeeOrder->processSyncUploadOrderStatus($chunks);
            if (isset($response) && $response == true) {
                $this->confirmations[] = isset($response['message']) ?
                    $response['message'] : "Sync Status Updated Successfully ";
            } else {
                $this->errors[] = isset($response['message']) ?
                    $response['message'] : "Sync Status not Updated ";
            }
        } else {
            $this->errors[] = Tools::displayError('Please Select Order');
        }
    }

    public function ajaxProcessShipOrder()
    {
        $CedShopeeOrder = new CedShopeeOrder;
        $order_id = Tools::getValue('ordersn');
        $tracking_no = Tools::getValue('tracking_number');
        $ship_data = array(
            'ordersn' => $order_id,
            'tracking_number' => $tracking_no
        );
        $response = $CedShopeeOrder->shipOrder($ship_data);
        if (isset($response) && !empty($response)) {
            die(json_encode($response));
        } else {
            $response = array('success' => false, 'message' => 'No Response from Shopee');
            die(json_encode($response));
        }
    }

    public function ajaxProcessCancelOrder()
    {
        $CedShopeeOrder = new CedShopeeOrder;
        $db = Db::getInstance();
        $order_id = Tools::getValue('ordersn');
        $cancel_reason = Tools::getValue('cancel_reason');
        $Sql = $db->executeS("SELECT `order_data` FROM " . _DB_PREFIX_ .
            "cedshopee_order WHERE shopee_order_id='" . $order_id . "'");
        $cancel_data = array(
            'order_sn' => $order_id,
            'cancel_reason' => $cancel_reason,
            'item_list' => array(
                'item_id' => $Sql[0]['order_data']['item_list']['item_id'],
                'model_id' => $Sql[0]['order_data']['item_list']['model_id']
            )
        );
        $response = $CedShopeeOrder->cancelOrder($cancel_data);
        if (isset($response) && !empty($response)) {
            die(json_encode($response));
        } else {
            $response = array('success' => false, 'message' => 'No Response from Shopee');
            die(json_encode($response));
        }
    }
}
