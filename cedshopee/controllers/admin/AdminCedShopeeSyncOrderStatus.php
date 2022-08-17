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

require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';
require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProduct.php';
require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeOrder.php';

class AdminCedShopeeSyncOrderStatusController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();
        $CedShopeeOrder = new CedShopeeOrder;
        $productIds = array();
        $limte = 'Bulk';
        $result = $CedShopeeOrder->syncOrderStatus($limte);
        if (isset($result) && is_array($result) && !empty($result)) {
            foreach ($result as $res) {
                $productIds[] = $res;
            }
        }

        $this->context->smarty->assign(array(
            'upload_array' => addslashes(json_encode($productIds))
        ));
        $link = new LinkCore();
        $controllerUrl = $link->getAdminLink('AdminCedShopeeSyncOrderStatus');
        $token = $this->token;
        $this->context->smarty->assign(array('controllerUrl' => $controllerUrl));
        $this->context->smarty->assign(array('token' => $token));
        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/order/bulk_sync_order_status.tpl'
        );
        $this->context->smarty->assign(array(
            'content' => $this->content . $content
        ));
    }

    public function ajaxProcessBulkSyncOrderStatus()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $CedShopeeOrder = new CedShopeeOrder;
        try {
            if (is_array(Tools::getValue('selected')) && count(Tools::getValue('selected'))) {
                $product_ids = Tools::getValue('selected');
                $errors = array();
                $successes = array();
                $response = $CedShopeeOrder->processSyncUploadOrderStatus($product_ids);
                if (isset($response)) {
                    $successes[] = 'Sync Status Updated Successfully <br>';
                } else {
                    $errors[] = 'No new Shopee order(s) found to Sync Status. ';
                }
                die(json_encode(
                    array(
                        'status' => true,
                        'response' => array(
                            'success' => $successes,
                            'errors' => $errors,
                        )
                    )
                ));
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                'AdminCedShopeeSyncOrderStatusController::SyncAll',
                'Exception',
                $e->getMessage(),
                $e->getMessage(),
                true
            );
            die(json_encode(array(
                'status' => true,
                'message' => $e->getMessage()
            )));
        }
    }
}
