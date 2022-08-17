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

include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php');
include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeProduct.php');
include_once(_PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeOrder.php');

class CedShopeeCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $urlkey = $_GET['secure_key'];
        $module_secure_key = Configuration::get('CEDSHOPEE_CRON_SECURE_KEY');
        $controller = $_GET['controller'];
        $module = $_GET['module'];
        if ($urlkey != $module_secure_key && !empty($urlkey) && $controller == 'cron' && $module == 'cedshopee') {
            die('Secure key does not matched');
        }
        if (Tools::getValue('method') == 'uploadProduct') {
            try {
                $CedShopeeLibrary = new CedShopeeLibrary();
                $CedShopeeProduct = new CedShopeeProduct;

                $db = Db::getInstance();
                $product_ids = array();

                $res = $db->executeS("SELECT `product_id` FROM `" . _DB_PREFIX_ . "cedshopee_profile_products`");
                if (isset($res) && !empty($res) && is_array($res)) {
                    foreach ($res as $id) {
                        $product_ids[] = $id['product_id'];
                    }
                }
                $product_ids = array_unique($product_ids);
                $product_ids = array_values($product_ids);

                if (is_array($product_ids) && count($product_ids)) {
                    $errors = array();
                    $successes = array();
                    $response = $CedShopeeProduct->uploadProducts($product_ids);

                    if (isset($response) && is_array($response)) {
                        if (isset($response['success']) && $response['success'] == true) {
                            $successes[] = $response['success'] . '<br>';
                        } else {
                            $errors[] = $response['error'];
                        }
                    }
                    $CedShopeeLibrary->log(
                        'Cron uploadProduct',
                        'Result',
                        'Upload Product Cron Result',
                        json_encode(
                            array(
                                'status' => true,
                                'response' => array(
                                    'success' => $successes,
                                    'errors' => $errors,
                                )
                            )
                        ),
                        true
                    );

                    $res = array(
                        'status' => true,
                        'response' => array(
                            'success' => $successes,
                            'errors' => $errors,
                        )
                    );
                } else {
                    $res = array(
                        'status' => false,
                        'message' => 'Please Select Product to Upload Product'
                    );
                }
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    'Cron uploadProduct',
                    'Exception',
                    'Upload Product Cron Exception',
                    json_encode(
                        array(
                            'success' => false,
                            'message' => $e->getMessage()
                        )
                    ),
                    true
                );
                $res = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
            print_r($res); die;
        }

        ini_set('memory_limit', '2048M');
        if (Tools::getIsset('method') && Tools::getValue('method') == 'updateInventory') {
            try {
                $CedShopeeLibrary = new CedShopeeLibrary();
                $CedShopeeProduct = new CedShopeeProduct;
                $db = Db::getInstance();
                $product_ids = $res = $success = $fail = array();
                $response = $db->executeS("SELECT `product_id` FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products`");
                if (isset($response) && !empty($response) && is_array($response)) {
                    foreach ($response as $id) {
                        $product_ids[] = $id['product_id'];
                    }
                }
                $product_ids = array_unique($product_ids);
                $product_ids = array_values($product_ids);
                if (is_array($product_ids) && count($product_ids)) {
                    foreach ($product_ids as $product_id) {
                        $result = $CedShopeeProduct->updateInventory($product_id);
                        if (isset($result['item'])) {
                            $success[] = $result['item'];
                        } elseif (isset($result['error'])) {
                            $fail[] = $result['error'];
                        }
                    }
                    if (!empty($success) && !empty($fail)) {
                        $res = array(
                            'status' => false, 'error' => implode("<br/>", $fail),
                            'success' => implode("<br/>", $success)
                        );
                    } elseif (!empty($success)) {
                        $res = array('status' => true, 'success' => implode("<br/>", $success));
                        //$res = array('status' => true, 'success' => 'Quantity Updated Successfully!');
                    } elseif (!empty($fail)) {
                        $res = array('status' => false, 'error' => implode("<br/>", $fail));
                    } else {
                        $res = array('status' => false, 'message' => 'Unable to update data.');
                    }
                    $CedShopeeLibrary->log(
                        'Cron uploadInventory',
                        'Response',
                        'Update Inventory Cron Response',
                        json_encode($res),
                        true
                    );
                    echo '<pre>';
                    print_r($res);
                    die;
                } else {
                    $res = array(
                        'status' => false,
                        'message' => 'Please Select Product to Update Inventory'
                    );
                }
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    'Cron uploadInventory',
                    'Response',
                    'Update Inventory Cron Response',
                    json_encode(array(
                        'status' => false,
                        'message' => 'Please Select Product to Update Inventory'
                    )),
                    true
                );
                $res = array(
                    'status' => false,
                    'message' => 'Please Select Product to Update Inventory'
                );
            }
            print_r($res); die;
        }

        if (Tools::getValue('method') == 'updatePrice') {
            try {
                $CedShopeeLibrary = new CedShopeeLibrary();
                $CedShopeeProduct = new CedShopeeProduct;

                $db = Db::getInstance();
                $product_ids = $success = $fail = $response = $res = array();
                $response = $db->executeS("SELECT `product_id` FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products`");
                if (isset($response) && !empty($response) && is_array($response)) {
                    foreach ($response as $id) {
                        $product_ids[] = $id['product_id'];
                    }
                }

                $product_ids = array_unique($product_ids);
                $product_ids = array_values($product_ids);
                if (is_array($product_ids) && count($product_ids)) {
                    foreach ($product_ids as $product_id) {
                        $result = $CedShopeeProduct->updatePrice($product_id);
                        if (isset($result['item'])) {
                            $success[] = $result['item'];
                        } elseif (isset($result['error'])) {
                            $fail[] = $result['error'];
                        }
                    }
                    if (!empty($success) && !empty($fail)) {
                        $res = array(
                            'status' => false, 'error' => implode("<br/>", $fail),
                            'success' => implode("<br/>", $success)
                        );
                    } elseif (!empty($success)) {
                        $res = array('status' => true, 'success' => implode("<br/>", $success));
                    } elseif (!empty($fail)) {
                        $res = array('status' => false, 'error' => implode("<br/>", $fail));
                    } else {
                        $res = array('status' => false, 'message' => 'Unable to update data.');
                    }
                    $CedShopeeLibrary->log(
                        'Cron uploadPrice',
                        'Response',
                        'Update Price Cron Response',
                        json_encode($res),
                        true
                    );
                    echo '<pre>';
                    print_r($res);
                    die;
                } else {
                    $res = array(
                        'status' => false,
                        'message' => 'Please Select Product to Upload Price'
                    );
                }
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    'Cron uploadProduct',
                    'Exception',
                    'Upload Product Cron Exception',
                    json_encode(
                        array(
                            'success' => false,
                            'message' => $e->getMessage()
                        )
                    ),
                    true
                );
                $res = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
            print_r($res); die;
        }

        if (Tools::getValue('method') == 'fetchorder') {
            try {
                $CedShopeeOrder = new CedShopeeOrder();
                $CedShopeeLibrary = new CedShopeeLibrary();

                $res = $CedShopeeOrder->fetchOrder();
                $CedShopeeLibrary->log(
                    'CronOrderFetch',
                    'Info',
                    'Cron For Order Fetch',
                    json_encode(array('success' => true, 'message' => $res)),
                    true
                );
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    'CronOrderFetch',
                    'Exception',
                    'CronOrderFetch Exception',
                    json_encode(
                        array(
                            'success' => false,
                            'message' => $e->getMessage()
                        )
                    ),
                    true
                );
                $res = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
            print_r($res); die;
        }

        if (Tools::getValue('method') == 'syncorderstatus') {
            try {
                $CedShopeeOrder = new CedShopeeOrder();
                $CedShopeeLibrary = new CedShopeeLibrary();
                $limit = 'All';
                $res = $CedShopeeOrder->syncOrderStatus($limit);
                $CedShopeeLibrary->log(
                    'CronSyncOrderStatus',
                    'Info',
                    'Cron For Sync Order Status',
                    json_encode(array('success' => true, 'message' => $res)),
                    true
                );
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    'CronSyncOrderStatus',
                    'Exception',
                    'Cron For Sync Order Status Exception',
                    json_encode(
                        array(
                            'success' => false,
                            'message' => $e->getMessage()
                        )
                    ),
                    true
                );
                $res = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
            echo "<pre>";
            print_r($res);
            die();
        }
        die('Done');
    }
}
