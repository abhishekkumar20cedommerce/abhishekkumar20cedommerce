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

class CedShopeeOrder
{
    public function syncOrderStatus($limit)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $status = $CedShopeeLibrary->isEnabled();
        $url = 'order/get_order_list';
        $params = array();
        $params['order_status'] = 'ALL'; // READY_TO_SHIP
        $pagination_entries_per_page = 100;
        if (!isset($params['page_size'])) {
            $params['page_size'] = $pagination_entries_per_page;
        }
        if (!isset($params['pagination_offset'])) {
            $params['pagination_offset'] = 0;
        }
        $created_from = configuration::get('CEDSHOPEE_ORDER_CREATED_FROM');
        $params['time_range_field'] = 'create_time';
        $params['time_from'] = strtotime($created_from);
        $params['time_to'] = time();
        try {
            if ($status) {
                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Params',
                    'Fetch Order Params',
                    json_encode(array(
                        'url' => $url,
                        'params' => $params,
                    )),
                    true
                );
                $response = $CedShopeeLibrary->getRequest($url, $params);
                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Response',
                    'Fetch Order Response',
                    json_encode($response),
                    true
                );
                if (is_array($response)
                    && isset($response['response']['order_list'])
                    && count($response['response']['order_list'])
                ) {
                    $count = 0;
                    $order_ids_created = array();
                    $orders = $response['response']['order_list'];
                    if (isset($orders) && $orders) {
                        $order_to_fetch = array();
                        foreach ($orders as $order) {
                            $shopeeOrderId = $order['order_sn'];
                            $already_exist = $this->isShopeeOrderAlreadyExist($shopeeOrderId);
                            if ($already_exist) {
                                $count++;
                                $order_to_fetch[] = $shopeeOrderId;
                            } else {
                                continue;
                            }
                        }
                        krsort($order_to_fetch);
                        $sorted_order = array_values($order_to_fetch);

                        if (isset($limit) && $limit == 'All') {
                            $this->chunkSortedOrder($sorted_order);
                        }
                        if (isset($limit) && $limit == 'Bulk') {
                            return $sorted_order;
                        }
                    }
                    if (count($order_ids_created) == 0) {
                        return array(
                            'success' => false,
                            'message' => 'sync status updated successfully'
                        );
                    } else {
                        return array(
                            'success' => true,
                            'message' => 'sync status updated successfully '
                        );
                    }
                } else {
                    return array(
                        'success' => false,
                        'message' => $response['message']
                    );
                }
            } else {
                return array('success' => false, 'message' => 'Module is not enabled.');
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Update Order Status  Exception',
                json_encode(array(
                    'success' => false,
                    'message' => $e->getMessage()
                )),
                true
            );
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    public function fetchOrder()
    {


        $CedShopeeLibrary = new CedShopeeLibrary;
        $status = $CedShopeeLibrary->isEnabled();
        $url = 'order/get_order_list';
        $params = array();
        $params['order_status'] = 'ALL'; // READY_TO_SHIP

        $pagination_entries_per_page = 100;
        if (!isset($params['page_size'])) {
            $params['page_size'] = $pagination_entries_per_page;
        }

        if (!isset($params['pagination_offset'])) {
            $params['pagination_offset'] = 0;
        }
        $created_from = configuration::get('CEDSHOPEE_ORDER_CREATED_FROM');
        //$created_to = configuration::get('CEDSHOPEE_ORDER_CREATED_TO');
        $params['time_range_field'] = 'create_time';

        $params['time_from'] = strtotime($created_from);
//        $params['time_from'] = strtotime(" -10 days ");
        $params['time_to'] = time();

        try {
            if ($status) {
                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Params',
                    'Fetch Order Params',
                    json_encode(array(
                        'url' => $url,
                        'params' => $params,
                    )),
                    true
                );

                $response = $CedShopeeLibrary->getRequest($url, $params);

                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Response',
                    'Fetch Order Response',
                    json_encode($response),
                    true
                );
                if (
                    is_array($response)
                    && isset($response['response']['order_list'])
                    && count($response['response']['order_list'])
                ) {
                    $count = 0;
                    $order_ids_created = array();
                    $orders = $response['response']['order_list'];
                    if (isset($orders) && $orders) {
                        $order_to_fetch = array();
                        foreach ($orders as $order) {
                            $shopeeOrderId = $order['order_sn'];
                            $already_exist = $this->isShopeeOrderAlreadyExist($shopeeOrderId);

                            if ($already_exist) {
                                continue;
                            } else {
                                $count++;
                                $order_to_fetch[] = $shopeeOrderId;
                            }
                        }
                        krsort($order_to_fetch);
                        $sorted_order = array_values($order_to_fetch);
                        $orders_chunk  = array_chunk($sorted_order,50);
                        foreach ($orders_chunk as $chunk_values) {
                            $orders_data = $this->fetchOrderDetails($chunk_values);
                            if (isset($orders_data) && count($orders_data) > 0) {
                                foreach ($orders_data['response']['order_list'] as $order_data) {
                                    if ($order_data['order_status'] == 'UNPAID') {
                                        continue;
                                    }
                                    if (isset($order_data['order_sn']) && $order_data['order_sn']) {
                                        $res = $this->prepareOrderData($order_data);
                                        if ($res) {
                                            $order_ids_created[] = $res;
                                        }
                                    }
                                }

                            }
                        }
                    }
                    if (count($order_ids_created) == 0) {
                        return array(
                            'success' => false,
                            'message' => 'No new Shopee order(s) found. Once check Rejected List too.'
                        );
                    } else {
                        return array(
                            'success' => true,
                            'message' => 'Order ID(s) - ' . implode(', ', $order_ids_created)
                                . ' fetched successfully. Once check Rejected List too.'
                        );
                    }
                } else {
                    return array(
                        'success' => false,
                        'message' => $response['message']
                    );
                }
            } else {
                return array('success' => false, 'message' => 'Module is not enabled.');
            }
        } catch (\Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Fetch Order Exception',
                json_encode(array(
                    'success' => false,
                    'message' => $e->getMessage()
                )),
                true
            );
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    public function isShopeeOrderAlreadyExist($shopeeOrderId = 0, $data = array())
    {
        $db = Db::getInstance();
        $isExist = false;
        if ($shopeeOrderId) {
            $sql = "SELECT `id` FROM `" . _DB_PREFIX_ . "cedshopee_order`
             WHERE `shopee_order_id` = '" . pSQL($shopeeOrderId) . "'";
            $result = $db->ExecuteS($sql);
            if (is_array($result) && count($result)) {
                $isExist = true;
                if (isset($data) && !empty($data) && is_array($data)) {
                    $status = isset($data['order_status']) ? $data['order_status'] : 'Created';
                    $db->update(
                        'cedshopee_order',
                        array(
                            'status' => pSQL($status),
                            'order_data' => pSQL(json_encode($data))
                        ),
                        'shopee_order_id="' . pSQL($shopeeOrderId) . '"'
                    );
                }
            }
        }
        return $isExist;
    }

    public function prepareOrderData($data = array())
    {
        $shopee_order_id = $data['order_sn'];
        $shipment = $data['recipient_address'];
        $orderDate = date('Y-m-d', $data['create_time']);
        $status = isset($data['order_status']) ? $data['order_status'] : 'Created';
        if (!$this->isShopeeOrderAlreadyExist($shopee_order_id, $data)) {
            $prestashopOrderId = $this->createPrestashopOrder($data);
            if ($prestashopOrderId) {
                $db = Db::getInstance();
                $db->insert(
                    'cedshopee_order',
                    array(
                        'order_place_date' => pSQL($orderDate),
                        'prestashop_order_id' => pSQL($prestashopOrderId),
                        'status' => pSQL($status),
                        'order_data' => pSQL(json_encode($data)),
                        'shipment_data' => pSQL(json_encode($shipment)),
                        'shopee_order_id' => pSQL($shopee_order_id),
                        'shipment_request_data' => pSQL(json_encode($shipment)),
                        'shipment_response_data' => pSQL(json_encode($shipment))
                    )
                );
                return $prestashopOrderId;
            }
        }
    }

    public function fetchOrderDetails($order_ids)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $order_data = array();
        $orders = '';
        $params = array();
        if (isset($order_ids) && count($order_ids)) {
            $url = 'order/get_order_detail';
            foreach ($order_ids as $orderID) {
                $orders .= $orderID . ',';
            }
            $ordewrs = substr_replace($orders, "", -1);
            $params['order_sn_list'] = $ordewrs;
            $order_data = $CedShopeeLibrary->getRequest($url, $params);
        }
        return $order_data;
    }

    public function createPrestashopOrder($orderData)
    {
        $contexts = Context::getContext();
        $shopeeOrderId = $orderData['order_sn'];
        $idLang = Context::getContext()->language->id;
        $nameArr = preg_replace('/[^A-Za-z0-9]/', ' ', $orderData['recipient_address']['name']);
        $nameArr = preg_replace('/[0-9]/', '', $nameArr);
        $nameArr = preg_split('/(?=[A-Z])/', $nameArr, -1, PREG_SPLIT_NO_EMPTY);
        $firstName = isset($nameArr[0]) && !empty($nameArr[0]) ? $nameArr[0] : 'Shopee';
        if (isset($nameArr[2]) || isset($nameArr[3])) {
            $lastName = $nameArr[1] . ' ' . $nameArr[2];
            if (isset($nameArr[3])) {
                $lastName .= ' '. $nameArr[3];
            }
        } else {
            $lastName = isset($nameArr[1]) && !empty($nameArr[1]) ? $nameArr[1] : 'Customer';
        }
        if (Configuration::get('CEDSHOPEE_ORDER_EMAIL')) {
            $email = Configuration::get('CEDSHOPEE_ORDER_EMAIL');
        } else {
            $email = isset($orderData['email']) ? $orderData['email'] : '';
            if (empty($email)) {
                if (isset($orderData['buyer_username']) && !empty($orderData['buyer_username'])) {
                    $email = $orderData['buyer_username'] . '@shopee.com';
                } else {
                    $email = $shopeeOrderId . '@shopee.com';
                }
            }
        }
        $idCustomer = 0;
        if ((int)Configuration::get('CEDSHOPEE_CUSTOMER_ID')) {
            $config_id_customer = (int)Configuration::get('CEDSHOPEE_CUSTOMER_ID');
            $customer = new Customer($config_id_customer);
            if (isset($customer->id) && $customer->id) {
                $idCustomer = (int)$customer->id;
            }
        } elseif (Customer::customerExists($email)) {
            $customer = Customer::getCustomersByEmail($email);
            if (isset($customer[0]) && isset($customer[0]['id_customer']) && $customer[0]['id_customer']) {
                $idCustomer = (int)$customer[0]['id_customer'];
            }
        }
        // Adding Customer in prestashop
        if (!$idCustomer) {
            $new_customer = new Customer();
            $new_customer->email = $email;
            $new_customer->lastname = $lastName;
            $new_customer->firstname = $firstName;
            $new_customer->passwd = 'shopee';
            $new_customer->add();
            $idCustomer = (int)$new_customer->id;
        }
        $contexts->customer = new Customer($idCustomer);
        //Adding Shipping Address detail in prestashop
        $state = isset($orderData['recipient_address']['state']) ? $orderData['recipient_address']['state'] : '';
        $country = isset($orderData['recipient_address']['region']) ? $orderData['recipient_address']['region'] : '';
        $getLocalizationDetails = $this->getLocalizationDetails($state, $country);
        $idCountry = $getLocalizationDetails['country_id'];
        $idState = $getLocalizationDetails['zone_id'];
        if (!validate::isAddress($orderData['recipient_address']['full_address'])) {
            $firstaddress = preg_replace(
                '/[^A-Za-z0-9]/',
                ' ',
                $orderData['recipient_address']['full_address']
            );
        } else {
            $firstaddress = $orderData['recipient_address']['full_address'];
        }
        $addressShipping = new Address();
        $addressShipping->id_customer = $idCustomer;
        $addressShipping->id_country = $idCountry;
        $addressShipping->alias = $shopeeOrderId . ' ' . time();
        $addressShipping->firstname = $firstName;
        $addressShipping->lastname = $lastName;
        $addressShipping->id_state = $idState;
        $addressShipping->address1 = Tools::substr($firstaddress, 0, 128);
        $addressShipping->address2 = '';
        $addressShipping->postcode = isset($orderData['recipient_address']['zipcode']) ?
            $orderData['recipient_address']['zipcode'] : '';
        $addressShipping->city = isset($orderData['recipient_address']['city']) ?
            $orderData['recipient_address']['city'] : '';
        $addressShipping->add();
        $idAddressShipping = $addressShipping->id;
        //Adding Delivery Address detail in prestashop
        $addressInvoice = new Address();
        $addressInvoice->id_customer = $idCustomer;
        $addressInvoice->id_country = $idCountry;
        $addressInvoice->alias = $shopeeOrderId . ' ' . time();
        $addressInvoice->firstname = $firstName;
        $addressInvoice->lastname = $lastName;
        $addressInvoice->id_state = $idState;
        $addressInvoice->address1 =  Tools::substr($firstaddress, 0, 128);
        $addressShipping->address2 = '';
        $addressInvoice->postcode = isset($orderData['recipient_address']['zipcode']) ?
            $orderData['recipient_address']['zipcode'] : '';
        $addressInvoice->city = isset($orderData['recipient_address']['city']) ?
            $orderData['recipient_address']['city'] : '';
        $addressInvoice->add();
        $idAddressInvoice = $addressInvoice->id;
        $paymentModule = Configuration::get('CEDSHOPEE_ORDER_PAYMENT');
        $moduleId = 0;
        $modulesList = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
                  FROM `' . _DB_PREFIX_ . 'module` m
                  LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON hm.`id_module` = m.`id_module`
                  LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON hm.`id_hook` = h.`id_hook`
                  GROUP BY hm.id_hook, hm.id_module ORDER BY hm.`position`, m.`name` DESC'
        );
        foreach ($modulesList as $module) {
            $moduleObj = Module::getInstanceById($module['id_module']);
            if (isset($moduleObj->name) && $moduleObj->name == $paymentModule) {
                $moduleId = $module['id_module'];
                break;
            }
        }
        $context = (array)$contexts;
        $currency = isset($context['currency']) ? (array)$context['currency'] : $orderData['currency'];
        if (Configuration::get('PS_CURRENCY_DEFAULT')) {
            $idCurrency = Configuration::get('PS_CURRENCY_DEFAULT');
        } else {
            $idCurrency = isset($currency['id']) ? $currency['id'] : '0';
        }
        if (!$idCurrency) {
            $currencyId = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'SELECT `id_currency` FROM `' . _DB_PREFIX_ . 'module_currency`
                WHERE `id_module` = ' . (int)$moduleId
            );
            $idCurrency = isset($currencyId['0']['id_currency']) ? $currencyId['0']['id_currency'] : 0;
        }
        $cart = new Cart();
        $cart->id_customer = $idCustomer;
        $cart->id_address_delivery = $idAddressShipping;
        $cart->id_address_invoice = $idAddressInvoice;
        $cart->id_currency = (int)$idCurrency;
        $cart->id_carrier = (int) Configuration::get('CEDSHOPEE_ORDER_CARRIER');
        $cart->recyclable = 0;
        $cart->gift = 0;
        $cart->add();
        $cartId = (int)($cart->id);
        $orderTotal = 0;
        $final_item_cost = 0;
        $total_vat = 0;
        $shippingCost = isset($orderData['estimated_shipping_fee']) ? (float)$orderData['estimated_shipping_fee'] : 0;
        $orderTotal += $shippingCost;
        if (isset($orderData) && isset($orderData['item_list'])) {
            $productArray = array();
            $order_items = $orderData['item_list'];
            foreach ($order_items as $item) {
                $item_sku = isset($item['item_sku']) ? $item['item_sku'] : '';
                $variation_sku = isset($item['model_sku']) ? $item['model_sku'] : '';
                $sku = isset($variation_sku) ? $variation_sku : $item_sku;
                $id_product = $this->getProductIdByReference($sku);
                if (count($id_product) > 1) {
                    $id_product = $this->getVariantProductIdByReference($sku);
                } elseif (count($id_product) == 1) {
                    $id_product = $id_product[0]['id_product'];
                }
                if (!$id_product) {
                    if (!empty($item_sku)) {
                        $id_product = $this->getVariantProductIdByReference($sku);
                    } else {
                        $id_product = $this->getVariantProductIdByReference($sku);
                    }
                }
                if (!empty($item_sku)) {
                    $id_product_attribute = $this->getProductAttributeIdByReference($sku);
                } else {
                    $id_product_attribute = $this->getProductAttributeIdByReference($sku);
                }

                $qty = isset($item['model_quantity_purchased']) ? $item['model_quantity_purchased'] : '0';
                $context = Context::getContext();
                $context->cart = new Cart();
                $producToAdd = new Product(
                    $id_product,
                    true,
                    (int) $idLang,
                    (int)$context->shop->id,
                    $context
                );
                $sku = isset($item_sku) ? $item_sku : $variation_sku;
                if (!$producToAdd->id) {
                    $this->orderErrorInformation(
                        $sku,
                        $shopeeOrderId,
                        "PRODUCT ID" . $id_product . " DOES NOT EXIST",
                        $orderData
                    );

                    $cart->delete();
                    return false;
                }
                if (!$producToAdd->checkQty((int)$qty)) {
                    $availableQuantity = StockAvailable::getQuantityAvailableByProduct(
                        $id_product,
                        $id_product_attribute
                    );
                    if ($qty > $availableQuantity) {
                        $this->orderErrorInformation(
                            $sku,
                            $shopeeOrderId,
                            "REQUESTED QUANTITY FOR PRODUCT ID " . $id_product . " IS NOT AVAILABLE",
                            $orderData
                        );
                    }
                    $cart->delete();
                    return false;
                }
                if (!$cart->updateQty((int)($qty), (int)($id_product), (int)$id_product_attribute)) {
                    $this->orderErrorInformation(
                        $sku,
                        $shopeeOrderId,
                        "CAN NOT ADD PRODUCT IN CART FOR PRODUCT ID" . $id_product,
                        $orderData
                    );
                    $cart->delete();
                    return false;
                }
                $cart->update();
                $item_cost = isset($item['model_original_price']) ? (float)$item['model_original_price'] : 0;
                $item_disc_cost = isset($item['model_discounted_price']) ?
                    (float)$item['model_discounted_price'] : 0;
                $item_vat = $item_cost - $item_disc_cost;
                $productArray[$id_product] = array(
                    'price_tax_included' => $item_cost,
                    'quantity' => $qty,
                    'price_tax_excluded' => $item_disc_cost
                );
                $total_cost = $item_cost * (int)$qty;
                $total_vat += $item_vat * (int)$qty;
                $final_item_cost += (float)$total_cost;
            }
            $orderTotal += $final_item_cost;
            if (count($productArray)) {
                $extraVars = array();
                $extraVars['item_shipping_cost'] = $shippingCost;
                $extraVars['total_paid'] = $orderTotal;
                $extraVars['total_item_cost'] = $final_item_cost;
                $extraVars['total_item_tax'] = $total_vat;
                $extraVars['item_shipping_tax'] = $shippingCost;
                $extraVars['merchant_order_id'] = $shopeeOrderId;
                $extraVars['customer_reference_order_id'] = $shopeeOrderId;
                $secureKey = false;
                $id_shop = (int)$contexts->shop->id;
                $shop = new Shop($id_shop);
                $prestashop_order_id = $this->addOrderInPrestashop(
                    $cartId,
                    $idCustomer,
                    $idAddressShipping,
                    $idAddressInvoice,
                    Configuration::get('CEDSHOPEE_ORDER_CARRIER'),
                    $idCurrency,
                    $extraVars,
                    $productArray,
                    $secureKey,
                    $contexts,
                    $shop,
                    $paymentModule,
                    Configuration::get('CEDSHOPEE_ORDER_STATE_IMPORT'),
                    $orderData['order_status']
                );
                if (!empty($prestashop_order_id)) {
                    return $prestashop_order_id;
                }
            } else {
                return false;
            }
            return false;
        }
    }

    public function addOrderInPrestashop(
        $id_cart,
        $id_customer,
        $id_address_delivery,
        $id_address_invoice,
        $id_carrier,
        $id_currency,
        $extra_vars,
        $products,
        $secure_key,
        $context,
        $shop,
        $payment_module,
        $orderState,
        $shopeeStatus
    ) {
        $newOrder = new Order();
        try {
            $context->cart = new Cart($id_cart);
            $carrier = new Carrier($id_carrier, $context->cart->id_lang);
            $newOrder->id_address_delivery = $id_address_delivery;
            $newOrder->id_address_invoice = $id_address_invoice;
            $newOrder->id_shop_group = $shop->id_shop_group;
            $newOrder->id_shop = isset($shop->id) ? $shop->id : '';
            $newOrder->id_cart = $id_cart;
            $newOrder->id_currency = $id_currency;
            $newOrder->id_lang = $context->language->id;
            $newOrder->id_customer = $id_customer;
            $newOrder->id_carrier = $id_carrier;
            $newOrder->current_state = $orderState;
            $newOrder->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($context->customer->secure_key));
            $newOrder->payment = $payment_module ? $payment_module : 'Shopee Payment';
            $newOrder->module = 'cedshopee';
            $conversion_rate = 1;
            if (isset($context->currency) && isset($context->currency->conversion_rate)) {
                $conversion_rate = $context->currency->conversion_rate;
            }
            $newOrder->conversion_rate = $conversion_rate;
            $newOrder->recyclable = $context->cart->recyclable;
            $newOrder->gift = (int)$context->cart->gift;
            $newOrder->gift_message = $context->cart->gift_message;
            $newOrder->mobile_theme = $context->cart->mobile_theme;
            $newOrder->total_discounts = 0;
            $newOrder->total_discounts_tax_incl = 0;
            $newOrder->total_discounts_tax_excl = 0;
            $newOrder->total_paid = $extra_vars['total_paid'];
            $newOrder->total_paid_tax_incl = $extra_vars['total_paid'];
            $newOrder->total_paid_tax_excl = $extra_vars['total_paid'];
            $newOrder->total_paid_real = $extra_vars['total_paid'];
            $newOrder->total_products = $extra_vars['total_item_cost'];
            $newOrder->total_products_wt = $extra_vars['total_item_cost'];
            $newOrder->total_shipping = $extra_vars['item_shipping_cost'];
            $newOrder->total_shipping_tax_incl = $extra_vars['item_shipping_cost'];
            $newOrder->total_shipping_tax_excl = $extra_vars['item_shipping_cost'] - $extra_vars['item_shipping_tax'];
            if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
                $newOrder->carrier_tax_rate = $carrier->getTaxesRate(
                    new Address($context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
                );
            }
            $newOrder->total_wrapping = 0;
            $newOrder->total_wrapping_tax_incl = 0;
            $newOrder->total_wrapping_tax_excl = 0;
            $newOrder->invoice_date = '0000-00-00 00:00:00';
            $newOrder->delivery_date = '0000-00-00 00:00:00';
            $newOrder->valid = true;
            do {
                $reference = Order::generateReference();
            } while (Order::getByReference($reference)->count());
            $newOrder->reference = $extra_vars['customer_reference_order_id'];
            $newOrder->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
            $packageList = $context->cart->getProducts(true);

            foreach ($packageList as &$product) {
                if (array_key_exists($product['id_product'], $products)) {
                    $product['price'] = $products[$product['id_product']]['price_tax_excluded'];
                    $product['price_wt'] = $products[$product['id_product']]['price_tax_included'];
                    $product['total'] = $products[$product['id_product']]['price_tax_excluded'] *
                        $products[$product['id_product']]['quantity'];
                    $product['total_wt'] = $products[$product['id_product']]['price_tax_included'] *
                        $products[$product['id_product']]['quantity'];
                }
            }
            $orderItems = $packageList;
            $newOrder->product_list = $orderItems;
            if (!($newOrder->id) && $newOrder->product_list) {
                $res = $newOrder->add(true, false);
                if (!$res) {
                    PrestaShopLogger::addLog(
                        'Order cannot be created',
                        3,
                        null,
                        'Cart',
                        (int)$id_cart,
                        true
                    );
                    throw new PrestaShopException('Can\'t add Order');
                }
            }
            if ($newOrder->id_carrier) {
                $newOrderCarrier = new OrderCarrier();
                $newOrderCarrier->id_order = (int)$newOrder->id;
                $newOrderCarrier->id_carrier = (int)$newOrder->id_carrier;
                $newOrderCarrier->weight = (float)$newOrder->getTotalWeight();
                $newOrderCarrier->shipping_cost_tax_excl = $newOrder->total_shipping_tax_excl;
                $newOrderCarrier->shipping_cost_tax_incl = $newOrder->total_shipping_tax_incl;
                $newOrderCarrier->add();
            }
            if (isset($newOrder->product_list) && count($newOrder->product_list)) {
                foreach ($newOrder->product_list as $product_d) {
                    $order_detail = new OrderDetail();
                    $order_detail->id_order = (int)$newOrder->id;
                    $order_detail->id_order_invoice = $product_d['id_address_delivery'];
                    $order_detail->product_id = $product_d['id_product'];
                    $order_detail->id_shop = $product_d['id_shop'];
                    $order_detail->id_warehouse = 0;
                    $order_detail->product_attribute_id = $product_d['id_product_attribute'];
                    $order_detail->product_name = $product_d['name'];
                    $order_detail->product_quantity = $product_d['cart_quantity'];
                    $order_detail->product_quantity_in_stock = $product_d['quantity_available'];
                    $order_detail->product_price = $product_d['price'];
                    $order_detail->unit_price_tax_incl = $product_d['price_wt'];
                    $order_detail->unit_price_tax_excl = $product_d['price'];
                    $order_detail->total_price_tax_incl = $product_d['total_wt'];
                    $order_detail->total_price_tax_excl = $product_d['total'];
                    $order_detail->product_ean13 = $product_d['ean13'];
                    $order_detail->product_upc = $product_d['upc'];
                    $order_detail->product_reference = $product_d['reference'];
                    $order_detail->product_supplier_reference = $product_d['supplier_reference'];
                    $order_detail->product_weight = $product_d['weight'];
                    $order_detail->ecotax = $product_d['ecotax'];
                    $order_detail->discount_quantity_applied = $product_d['quantity_discount_applies'];
                    $o_res = $order_detail->add();
                    if (!$o_res) {
                        $newOrder->delete();
                        PrestaShopLogger::addLog(
                            'Order details cannot be created',
                            3,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                        throw new PrestaShopException('Can\'t add Order details');
                    }
                }
                Hook::exec(
                    'actionValidateOrder',
                    array(
                        'cart' => $context->cart,
                        'order' => $newOrder,
                        'customer' => $context->customer,
                        'currency' => $context->currency,
                        'orderStatus' => $orderState
                    )
                );
                $order_status = new OrderState(
                    $orderState,
                    (int)$context->language->id
                );

                foreach ($context->cart->getProducts() as $product) {
                    if ($order_status->logable) {
                        ProductSale::addProductSale(
                            (int)$product['id_product'],
                            (int)$product['cart_quantity']
                        );
                    }
                }

                // Set the order status
                try {
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$newOrder->id;
                    $new_history->changeIdOrderState($orderState, $newOrder, true);
                    $new_history->add(true, $extra_vars);
                    // Switch to back order if needed
                    if (Configuration::get('PS_STOCK_MANAGEMENT') && $order_detail->getStockState()) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$newOrder->id;
                        $history->changeIdOrderState(
                            Configuration::get(
                                $newOrder->valid ? 'PS_OS_OUTOFSTOCK_PAID' : 'PS_OS_OUTOFSTOCK_UNPAID'
                            ),
                            $newOrder,
                            true
                        );
                        $history->add();
                    }
                } catch (Exception $e) {
                    print_r($e->getMessage());
                    die();
                }

                $product_list = $newOrder->getProducts();

                foreach ($product_list as $product) {
                    $idProd = $product['product_id'];
                    $idProdAttr = $product['product_attribute_id'];
                    $qtyToReduce = (int)$product['product_quantity'] * -1;
                    if ($shopeeStatus !== 'shipped' && $shopeeStatus !== 'delivered') {
                        StockAvailable::updateQuantity($idProd, $idProdAttr, $qtyToReduce, $newOrder->id_shop);
                    }
                }
                if (isset($newOrder->id) && $newOrder->id) {
                    return $newOrder->id;
                } else {
                    $newOrder->delete();
                    return false;
                }
            }
            $newOrder->delete();
            return false;
        } catch (Exception $e) {
            $newOrder->delete();
        }
    }

    public function getLocalizationDetails($Statecode, $countryCode)
    {
        $db = Db::getInstance();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT c.id_country, cl.name FROM `" . _DB_PREFIX_ . "country` c
         LEFT JOIN `" . _DB_PREFIX_ . "country_lang` cl on (c.id_country = cl.id_country)
          WHERE `iso_code` LIKE '" . pSQL($countryCode) . "' AND cl.id_lang ='" . (int)$default_lang . "'";
        $Execute = $db->ExecuteS($sql);
        if (is_array($Execute) && count($Execute) && isset($Execute['0'])) {
            $country_id = 0;
            $country_name = '';
            if (isset($Execute['0']['id_country']) && $Execute['0']['id_country']) {
                $country_id = $Execute['0']['id_country'];
                $country_name = $Execute['0']['name'];
            }
            if ($country_id) {
                $Execute = $db->ExecuteS("SELECT `id_state`,`name` FROM 
                 `" . _DB_PREFIX_ . "state` WHERE `id_country`='" . (int)$country_id . "'
                  AND `name` LIKE '%" . pSQL($Statecode) . "%'");
                if (is_array($Execute) && count($Execute)) {
                    if (isset($Execute['0']['id_state']) && isset($Execute['0']['name'])) {
                        return array(
                            'country_id' => $country_id,
                            'zone_id' => $Execute['0']['id_state'],
                            'name' => $Execute['0']['name'],
                            'country_name' => $country_name
                        );
                    };
                } else {
                    return array(
                        'country_id' => $country_id,
                        'zone_id' => '',
                        'name' => '',
                        'country_name' => $country_name
                    );
                }
            } else {
                return array(
                    'country_id' => '',
                    'zone_id' => '',
                    'name' => '',
                    'country_name' => ''
                );
            }
        } else {
            return array(
                'country_id' => '',
                'zone_id' => '',
                'name' => '',
                'country_name' => ''
            );
        }
    }

    public function getProductIdByReference($merchant_sku)
    {
        if ($merchant_sku) {
            $db = Db::getInstance();
            return $db->executeS("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product` 
            WHERE `reference` = '" . pSQL($merchant_sku) . "' ");
        } else {
            return false;
        }
    }

    public function getVariantProductIdByReference($merchant_sku)
    {
        if ($merchant_sku) {
            $db = Db::getInstance();
            $res = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute`
            WHERE `reference`="' . pSQL($merchant_sku) . '" AND `id_product` = 0');
            if (!empty($res)) {
                foreach ($res as $valres) {
                    $db->execute("DELETE FROM "._DB_PREFIX_ ."product_attribute WHERE id_product_attribute = ".$valres['id_product_attribute']);
                }
            }
            return $db->getValue('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product_attribute`
            WHERE `reference`="' . pSQL($merchant_sku) . '" AND `id_product` != 0');
        } else {
            return false;
        }
    }

    public static function getProductAttributeIdByReference($merchant_sku)
    {
        if ($merchant_sku) {
            $db = Db::getInstance();
            return $db->getValue('SELECT `id_product_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute` 
            WHERE `reference`="' . pSQL($merchant_sku) . '"');
        } else {
            return false;
        }
    }

    public function orderErrorInformation($sku, $shopeeOrderId, $reason, $orderData)
    {
        $db = Db::getInstance();
        $sql_check_already_exists = "SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_order_error` 
        WHERE `merchant_sku`='" . pSQL($sku) . "' AND `shopee_order_id`='" . pSQL($shopeeOrderId) . "'";
        $Execute_check_already_exists = $db->ExecuteS($sql_check_already_exists);

        if (count($Execute_check_already_exists) == 0) {
            $sql_insert = "INSERT INTO `" . _DB_PREFIX_ . "cedshopee_order_error` (
            `merchant_sku`,`shopee_order_id`,`reason`,`order_data`)
            VALUES('" . pSQL($sku) . "','" . pSQL($shopeeOrderId) . "','" . pSQL($reason) . "',
            '" . pSQL(json_encode($orderData)) . "')";
            $db->Execute($sql_insert);
        }
        return true;
    }

    public function cancelOrder($params = array())
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $response = $CedShopeeLibrary->postRequest('order/cancel_order', $params);
        try {
            if (empty($response['error'])) {
                if (isset($response['response']) && $response['response']) {
                    return array('success' => true, 'response' => $response['message']);
                }
            } elseif (isset($response['error']) && !empty($response['error'])) {
                return array('success' => false, 'message' => $response['error']);
            }
        } catch (Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Cancel Order Exception',
                json_encode(array(
                    'success' => false,
                    'message' => $e->getMessage()
                )),
                true
            );
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    public function shipOrder($ship_data = null)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $trackingNumber = '';
        if (isset($ship_data['tracking_number'])) {
            $trackingNumber = $ship_data['tracking_number'];
        }
        $ordersn = '';
        if (isset($ship_data['ordersn'])) {
            $ordersn = $ship_data['ordersn'];
        }
        if ($trackingNumber && $ordersn) {
            try {
                $shiporderdata = array(
                    'order_sn' => $ordersn,
                    'dropoff' => array(
                        'tracking_no' => $trackingNumber
                    )
                );
                $params = $shiporderdata;
                $response = $CedShopeeLibrary->postRequest(
                    'logistics/ship_order',
                    $params
                );

                if (empty($response['error']) && $response['request_id']) {
                    $db = Db::getInstance();
                    $db->update(
                        'cedshopee_order',
                        array(
                            'status' => pSQL('SHIPPED')
                        ),
                        'shopee_order_id="' . pSQL($ordersn) . '"'
                    );
                    $idShipped = Configuration::get('CEDSHOPEE_ORDER_STATE_SHIPPED');
                    $this->updateOrderStatus($ordersn, $idShipped);
                    return array('success' => true, 'response' => json_encode($response));
                } else {
                    return array('success' => false, 'message' => $response['error']);
                }
            } catch (Exception $e) {
                $CedShopeeLibrary->log(
                    __METHOD__,
                    'Exception',
                    'Ship Order Exception',
                    json_encode(array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )),
                    true
                );

                return array('success' => false, 'message' => $e->getMessage());
            }
        } else {
            return array('success' => false, 'message' => 'Missing Order ID or Tracking no.');
        }
    }

    public function acceptOrder($shopee_order_id, $url = 'v3/orders')
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $response = $CedShopeeLibrary->postRequest($url . '/' . $shopee_order_id . '/acknowledge');
        try {
            if (isset($response['success']) && $response['success']) {
                $response = $response['response'];
            } else {
                return $response;
            }
            $response = json_decode($response, true);
            if (isset($response['error'])) {
                return array('success' => false, 'message' => $response['error']);
            }
            $idAck = Configuration::get('CEDSHOPEE_ORDER_STATE_ACKNOWLEDGE');
            $this->updateOrderStatus($shopee_order_id, $idAck);
            return $response;
        } catch (Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Accept Order Exception',
                json_encode(array(
                    'success' => false,
                    'message' => $e->getMessage()
                )),
                true
            );

            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    public function chunkSortedOrder($sorted_order)
    {
        $chunks = array_chunk($sorted_order, 50);
        $this->processSyncUploadOrderStatus($chunks);
        return true;
    }

    public function processSyncUploadOrderStatus($chunks)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        foreach ($chunks as $chunk_order) {
            $orders_data = $this->fetchOrderDetails($chunk_order);
            if (isset($orders_data) && count($orders_data) > 0) {
                foreach ($orders_data['response']['order_list'] as $order_data) {
                    if (isset($order_data['order_sn']) && $order_data['order_sn']) {
                        $ShopeeStatus = $CedShopeeLibrary->getShopeeStatus();
                        foreach ($ShopeeStatus as $status) {
                            if ($order_data['order_status'] == $status['name']) {
                                $mapped_status = Configuration::get('CEDSHOPEE_STATUS_MAPPING') ? json_decode(
                                    Configuration::get('CEDSHOPEE_STATUS_MAPPING'),
                                    true
                                ) : array();
                                foreach ($mapped_status as $mapped_status_ids) {
                                    if ($status['id_shopee_status'] == $mapped_status_ids['id_marketplace_carrier']) {
                                        $mapped_status_id = $mapped_status_ids['id_order_state'];
                                        $ordersn = $order_data['order_sn'];
                                        try {
                                            $this->updateOrderStatus($ordersn, $mapped_status_id);
                                        } catch (\Exception $e) {
                                            echo "<pre>";
                                            print_r($e->getMessage());
                                            die();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function updateOrderStatus($order_id, $id_order_state)
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        try {
            $db = Db::getInstance();
            $result = $db->ExecuteS("SELECT `prestashop_order_id` FROM `" . _DB_PREFIX_ . "cedshopee_order`
            where `shopee_order_id` = '" . pSQL($order_id) . "'");
            if (is_array($result) && count($result)) {
                $order_id = $result['0']['prestashop_order_id'];
            }
            $order_state = new OrderState($id_order_state);
            $order = new Order((int)$order_id);
            $history = new OrderHistory();
            $history->id_order = $order->id;
            $db->update(
                'orders',
                array(
                    'current_state' => pSQL($order_state->id),
                ),
                'id_order=' . (int) $order_id
            );
            $db->update(
                'order_history',
                array(
                    'id_order_state' => pSQL($order_state->id),
                ),
                'id_order=' . (int) $order_id
            );
        } catch (\Exception $e) {
            //            print_r( $e->getMessage()); die("okokk");
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                $e->getMessage(),
                '',
                true
            );
        }
    }

    public function getInvoice($prestaorder = array())
    {
        $result  = array();
        $finalresponse = array();
        $linioid = '';
        $params = array();
        if ($prestaorder) {
            foreach ($prestaorder as $pres_id) {
                $mlibreOrderId = $this->getMlibreOrderId($pres_id);
                if (!empty($mlibreOrderId)) {
                    $params['OrderItemIds'] = json_encode($mlibreOrderId);
                }
                if ($mlibreOrderId) {
                    $db = Db::getInstance();
                    $sipment_id = $db->executeS('SELECT `mlibre_shipment_id` FROM `' . _DB_PREFIX_ .
                        'cedmlibre_orders` WHERE `mlibre_order_id`= "' . pSQL($mlibreOrderId) . '"');
                    if (!empty($sipment_id)) {
                        $url = 'shipment_labels';
                        $mlibreOrderApi = new CedMlibreOrderApi();
                        $res = $mlibreOrderApi->getshippinglabel($url, $sipment_id[0]['mlibre_shipment_id']);
                        $res['mlibreOrderId'] = $mlibreOrderId;
                        $result[] = $res;
                    }
                }
            }
            $invoice_dir = _PS_MODULE_DIR_ . 'cedmlibre/invoice/';
            if (!is_dir($invoice_dir)) {
                mkdir($invoice_dir, '0777', true);
            }
            if (count($prestaorder) > 1 && count($result) > 1) {
                $random_no = strtotime(date('Y-m-d H:i:s'));
                $compressfilename = $invoice_dir . 'BulkShippingLabels_' . $random_no . '.zip';
                foreach ($result as $fileValue) {
                    if (isset($fileValue) && $fileValue['success'] == true) {
                        if (isset($fileValue) && !empty($fileValue)) {
                            if (isset($fileValue) && !empty($fileValue['PDF'])) {
                                $file = $fileValue['PDF'];
                                $filename = $invoice_dir . $fileValue['mlibreOrderId'] . '.pdf';
                                file_put_contents($filename, $file);
                                if (file_exists($filename)) {
                                    $linioid .= $fileValue['mlibreOrderId'] . ', ';
                                    $downloadLabel = Context::getContext()->shop->getBaseURL(true)
                                        . 'modules/cedmlibre/invoice/' . $fileValue['mlibreOrderId'] . '.pdf';
                                }
                                $zip = new ZipArchive();
                                $zip->open($compressfilename, ZipArchive::CREATE);
                                $zip->addFile($filename);
                                $zip->close();
                            } elseif (isset($fileValue) && !empty($fileValue['ZPL'])) {
                                $file = $fileValue['ZPL'];
                                $filename = $invoice_dir . $fileValue['mlibreOrderId'] . '.zip';
                                file_put_contents($filename, $file);
                                if (file_exists($filename)) {
                                    $linioid .= $fileValue['mlibreOrderId'] . ', ';
                                    $downloadLabel = Context::getContext()->shop->getBaseURL(true)
                                        . 'modules/cedmlibre/invoice/' . $fileValue['mlibreOrderId'] . '.zip';
                                    $db->update(
                                        'cedmlibre_orders',
                                        array(
                                            'invoice' => pSQL($downloadLabel)
                                        ),
                                        'mlibre_order_id="' . pSQL($fileValue['mlibreOrderId']) . '"'
                                    );
                                }
                                $zip = new ZipArchive();
                                $zip->open($compressfilename, ZipArchive::CREATE);
                                $zip->addFile($filename);
                                $zip->close();
                            }
                        }
                    } elseif (isset($fileValue) && $fileValue['success'] == false) {
                        $finalresponse[] = array('success' => false, 'message' => $fileValue['message']['message']);
                    }
                }
                if (file_exists($compressfilename)) {
                    $downloalLabel = Context::getContext()->shop->getBaseURL(true)
                        . 'modules/cedmlibre/invoice/' . 'BulkShippingLabels_' . $random_no . '.zip';
                    $finalresponse[] = array('success' => true, 'message' => 'Mlibre Order ID - ' . $linioid .
                        ' : Ship label downloaded successfully ' . ' <b><a href="' . $downloalLabel .
                        '" target="_blank"> click here </a></b>');
                }
                return $finalresponse;
            } else {
                if (isset($result) && !empty($result) && $result[0]['success'] == true) {
                    if (isset($result[0]['PDF']) && !empty($result)) {
                        $file = $result[0]['PDF'];
                        $filename = $invoice_dir . $mlibreOrderId . '.pdf';
                        file_put_contents($filename, $file);
                        if (file_exists($filename)) {
                            $downloadLabel = Context::getContext()->shop->getBaseURL(true)
                                . 'modules/cedmlibre/invoice/' . $mlibreOrderId . '.pdf';
                            $db->update(
                                'cedmlibre_orders',
                                array(
                                    'invoice' => pSQL($downloadLabel)
                                ),
                                'mlibre_order_id="' . pSQL($mlibreOrderId) . '"'
                            );
                            $finalresponse[] = array('success' => true, 'message' => 'Mlibre Order ID ' .
                                $mlibreOrderId . ' : Invoice downloaded successfully ' . '<a href="' . $downloadLabel .
                                '" target="_blank"> click here </a>');
                            return $finalresponse;
                        }
                    } elseif (isset($result[0]['ZPL']) && !empty($result)) {
                        $file = $result[0]['ZPL'];
                        $filename = $invoice_dir . $mlibreOrderId . '.zip';
                        file_put_contents($filename, $file);
                        if (file_exists($filename)) {
                            $downloadLabel = Context::getContext()->shop->getBaseURL(true)
                                . 'modules/cedmlibre/invoice/' . $mlibreOrderId . '.zip';
                            $db->update(
                                'cedmlibre_orders',
                                array(
                                    'invoice' => pSQL($downloadLabel)
                                ),
                                'mlibre_order_id="' . pSQL($mlibreOrderId) . '"'
                            );
                            $finalresponse[] = array('success' => true, 'message' => 'Mlibre Order ID ' .
                                $mlibreOrderId . ' : Invoice downloaded successfully ' . '<a href="' . $downloadLabel .
                                '" target="_blank"> click here </a>');
                            return $finalresponse;
                        }
                    }
                } elseif (isset($result['0']['message']) && isset($result['0']['message'])) {
                    $finalresponse[] = array('success' => false, 'message' => $result['0']['message']['message']);
                    return $finalresponse;
                } else {
                    $finalresponse[] = array(
                        'success' => false,
                        'message' => 'Something went wrong couldn\'t get invoice.'
                    );
                    return $finalresponse;
                }
            }
        } else {
            $finalresponse[] = array('success' => false, 'message' => 'No Order item ids to get invoice.');
            return $finalresponse;
        }
    }
}
