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

include_once  _PS_MODULE_DIR_.'cedshopee/classes/CedShopeeOrder.php';
include_once  _PS_MODULE_DIR_.'cedshopee/classes/CedShopeeLibrary.php';

class AdminCedShopeeFailedOrderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->bootstrap  = true;
        $this->table = 'cedshopee_order_error';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('cancel');
        $this->addRowAction('importorder');
        $this->fields_list = array(
            'id'       => array(
                'title' => 'ID',
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'merchant_sku'     => array(
                'title' => 'SKU',
                'type'  => 'text',
            ),
            'shopee_order_id' => array(
                'title' => 'Shopee Order Id',
                'type'  => 'text',
            ),
            'reason' => array(
                'title' => 'Reason',
                'type'  => 'text',
            ),
            'order_data' => array(
                'title' => 'Edit And Re-Import',
                'align' => 'text-center',
                'type' => 'text',
                'search' => false,
                'callback' => 'viewOrderButton',
            )
        );
        $this->bulk_actions = array(
            'cancel' => array('text' => 'Cancel Order', 'icon' => 'icon-power-off'),
        );
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeOrder = new CedShopeeOrder;
        if (Tools::getIsset('cancelorder') && Tools::getValue('cancelorder')) {
            $id = Tools::getValue('cancelorder');
            if ($id) {
                $shopeeOrder = $this->db->getValue(
                    'SELECT `shopee_order_id`,`order_data` FROM `'._DB_PREFIX_.'cedshopee_order_error` 
                    WHERE `id`="'.(int)($id).'"'
                );
                $status = $CedShopeeLibrary->isEnabled();
                if ($status) {
                    $params = array(
                        'order_sn' => $shopeeOrder[0]['shopee_order_id'],
                        'cancel_reason' => 'OUT_OF_STOCK',
                        'item_list' => array(
                            'item_id' => $shopeeOrder[0]['order_data']['item_list']['item_id'],
                            'model_id' => $shopeeOrder[0]['order_data']['item_list']['model_id']
                        )
                    );
                    $response = $CedShopeeOrder->cancelOrder($params);
                    if (isset($response['success'])&& $response['success'] == true) {
                        $this->confirmations[] = isset($response['message']) ?
                            $response['message']: "Order ".$shopeeOrder[0]['shopee_order_id']." cancelled successfully";
                    } else {
                        $this->errors[] = isset($response['message']) ?
                            $response['message']: "Order ".$shopeeOrder[0]['shopee_order_id']." can not be cancelled";
                    }
                }
            } else {
                $this->errors[] = Tools::displayError('Please Select Order');
            }
        }
        parent::__construct();
    }

    public function viewOrderButton($d, $data)
    {
        $data['order_data'] =  $d;
        $data['token'] = $this->token;
        $this->context->smarty->assign(
            $data
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/order/view_and_reimport.tpl'
        );
    }

    public function renderForm()
    {
        $link = new LinkCore();
        $redirect = $link->getAdminLink('AdminOrders');
        Tools::redirectAdmin($redirect);
    }

    public function ajaxProcessresubmitFeed()
    {
        $response = "json data is incorrect";
        $CedShopeeOrder = new CedShopeeOrder();
        if (Tools::getIsset('feed_content') && Tools::getIsset('id') &&
            Tools::getValue('id') && Tools::getValue('feed_content')
        ) {
            $id  = Tools::getValue('id');
            $orderArr = Tools::getValue('feed_content');
            try {
                $orderData = json_decode($orderArr, true);
                if ($orderData === null && json_last_error() !== JSON_ERROR_NONE) {
                    $response = "json data is incorrect";
                } else {
                    $shopee_OrderId = $orderData['order_sn'];

                    $orderAlreadyExist = $CedShopeeOrder->isShopeeOrderAlreadyExist($shopee_OrderId, $orderData);
                    if (!$orderAlreadyExist) {

                        $prestashop_id = $CedShopeeOrder->prepareOrderData($orderData);
                        if (empty($prestashop_id) && !isset($prestashop_id)) {
                            $response =  array('success' => false, 'message' =>'Fail to import order id - ' .
                                $shopee_OrderId . ' ');
                        } else {
                            $db = Db::getInstance();
                            $db->execute('DELETE FROM ' . _DB_PREFIX_ .
                                'cedshopee_order_error WHERE id=' . (int)$id);
                            $response = array(
                                'success' => true,
                                'message' => 'Failed order id : ' .$id .
                                    ' imported successfully with prestashop order Id: .' . $prestashop_id);
                        }
                    } else {
                        $response = array('success' => false,'message' =>'Order - ' . $id . ' already exist');
                    }
                }
            } catch (Exception $e) {
                $response = array('success' => false,'message' => $e->getMessage());
            }
        }
        die(json_encode($response));
    }

    public function postProcess()
    {
        if (Tools::getIsset('delete_failed_orders') && Tools::getValue('delete_failed_orders')) {
            $db = Db::getInstance();
            $sql = "TRUNCATE TABLE `"._DB_PREFIX_."cedshopee_order_error`";
            $res = $db->execute($sql);
            if ($res) {
                $this->confirmations[] = "Failed Orders Deleted Successfully";
            } else {
                $this->errors[] = "Failed To Delete";
            }
        }
        return parent::postProcess();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['delete_failed_orders'] = array(
                'href' => self::$currentIndex . '&delete_failed_orders=1&token=' . $this->token,
                'desc' => 'Delete All',
                'icon' => 'process-icon-eraser'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function processBulkCancel()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeOrder = new CedShopeeOrder;
        $ids = $this->boxes;
        if (Tools::getIsset($ids) && count($ids)) {
            foreach ($ids as $id) {
                if ($id) {
                    $shopeeOrder = $this->db->executeS(
                        'SELECT `order_data`,`shopee_order_id` FROM `'._DB_PREFIX_.'cedshopee_order_error` 
                        WHERE `id`="'.(int)($id).'"'
                    );
                    $status = $CedShopeeLibrary->isEnabled();
                    if ($status) {
                        if (isset($shopeeOrder)) {
                            $params = array(
                                'order_sn' => $shopeeOrder[0]['shopee_order_id'],
                                'cancel_reason' => 'OUT_OF_STOCK',
                                'item_list' => array(
                                    'item_id' => $shopeeOrder[0]['order_data']['item_list']['item_id'],
                                    'model_id' => $shopeeOrder[0]['order_data']['item_list']['model_id']
                                )
                            );
                        }
                        $response = $CedShopeeOrder->cancelOrder($params);
                        if (isset($response['success'])&& $response['success'] == true) {
                            $this->confirmations[] = isset($response['message']) ?
                                $response['message']: "Order ".$shopeeOrder[0]['shopee_order_id'].
                                " cancelled successfully";
                        } else {
                            $this->errors[] = isset($response['message']) ?
                                $response['message']: "Order ".$shopeeOrder[0]['shopee_order_id'].
                                " can not be cancelled";
                        }
                    }
                }
            }
        } else {
            $this->errors[] = Tools::displayError('Please Select Order');
        }
    }

    public function displayCancelLink($token = null, $id = null, $name = null)
    {
        if ($token && $name) {
            $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        } else {
            $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        }
        if (!array_key_exists('Cancel', self::$cache_lang)) {
            self::$cache_lang['Cancel'] = $this->l('Cancel', 'Helper');
        }
        $tpl->assign(array(
            'href' => Context::getContext()->link->getAdminLink(
                'AdminCedShopeeFailedOrder'
            ).'&cancelorder='.$id.'&id='.$id,
            'action' => self::$cache_lang['Cancel'],
            'id' => $id
        ));
        return $tpl->fetch();
    }
}
