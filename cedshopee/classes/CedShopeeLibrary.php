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

class CedShopeeLibrary
{
    public function __construct()
    {
        $this->api_mode = Configuration::get('CEDSHOPEE_MODE');
        if ($this->api_mode == '1') {
            $this->_api_url = Configuration::get('CEDSHOPEE_LIVE_API_URL');
            $this->partner_id = Configuration::get('CEDSHOPEE_LIVE_PARTNER_ID');
            $this->shop_id = Configuration::get('CEDSHOPEE_LIVE_SHOP_ID');
            $this->signature = Configuration::get('CEDSHOPEE_LIVE_SIGNATURE');
        } else {
            $this->_api_url = Configuration::get('CEDSHOPEE_SANDBOX_API_URL');
            $this->partner_id = Configuration::get('CEDSHOPEE_SANDBOX_PARTNER_ID');
            $this->shop_id = Configuration::get('CEDSHOPEE_SANDBOX_SHOP_ID');
            $this->signature = Configuration::get('CEDSHOPEE_SANDBOX_SIGNATURE');
        }
        $this->access_token = Configuration::get('CEDSHOPEE_ACCESS_TOKEN');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $flag = false;
        if (Configuration::get('CEDSHOPEE_ENABLE')) {
            $flag = true;
        }
        return $flag;
    }

    public function uploadImageByVersion2($params = array())
    {
        $url = "https://partner.shopeemobile.com/api/v2/media_space/upload_image";
        $response = null;
        $body = array();
        $parameter = array();
        $enable = $this->isEnabled();
        if ($enable) {
            try {
                $this->checkAccessToken();
                if (version_compare(phpversion(), '5.5.0', '>=') === false) {
                    $body['file'] = new CurlFile($params['image'], 'image/jpg', basename($params['image']));
                } elseif (function_exists('curl_file_create')) {
                    $body['file'] = curl_file_create($params['image'], 'image/jpg', basename($params['image']));
                } else {
                    $value = "@{$params['image']};filename=" . basename($params['image']) . ';type=image/jpg';
                    $body['file'] = $value;
                }
                $parameter['image'] =  $body['file'];
                $mergedata = array_merge(array(
                    'shop_id' => (int)$this->shop_id,
                    'partner_id' => (int)$this->partner_id,
                    'timestamp' => time(),
                ), $parameter);
                $jsonBody = $mergedata;
                $path = "media_space/upload_image";
                $merge_key = $this->partner_id . '/api/v2/' . $path . time() . $this->access_token . $this->shop_id;
                $sign = hash_hmac('sha256', $merge_key, $this->signature);
                $url = $url . "?partner_id=" . $this->partner_id . "&timestamp=" . time() . "&sign=" . $sign .
                    "&shop_id=" . $this->shop_id . "&access_token=" . $this->access_token;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $jsonBody,
                ));
                $response = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($response) {
                    $response = json_decode($response, true);
                }
                if ($httpcode != 200) {
                    return $response;
                }
                curl_close($curl);
                if ($response && ($httpcode == 200)) {
                    return $response;
                } else {
                    return '{}';
                }
            } catch (Exception $e) {
                $this->log(
                    __METHOD__,
                    'Exception',
                    'Post Request Exception',
                    json_encode(array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )),
                    true
                );
                return array('error' => 'exception', 'msg' => $e->getMessage());
            }
        }
    }

    public function getToken($url, $params)
    {
        $path = 'auth/token/get';
        $jsonBody = $this->createJsonBody($params);
        $url .= $path;
        $merge_key = $this->partner_id . '/api/v2/' . $path . time();
        $sign = hash_hmac('sha256', $merge_key, $this->signature);
        $url = $url . "?partner_id=" . $this->partner_id . "&timestamp=" . time() . "&sign=" . $sign;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($response) {
            $body = json_decode($response, true);
        }
        if ($httpcode != 200) {
            return $body;
        }
        curl_close($curl);
        if ($body && ($httpcode == 200)) {
            return $body;
        } else {
            return '{}';
        }
    }

    protected function checkAccessToken()
    {
        $db = Db::getInstance();
        $token_expires_in = $db->executeS("SELECT `date_upd` FROM  " . _DB_PREFIX_ .
            "configuration WHERE `name` = 'CEDSHOPEE_EXPIRE_IN'");
        if (!empty($token_expires_in)) {
            $token_fetched_time = $token_expires_in[0]['date_upd'];
            $current_time = date('Y-m-d H:i:s');
            $time1 = new DateTime($token_fetched_time);
            $time2 = new DateTime($current_time);
            $interval = $time1->diff($time2);
            $difference_time = ($interval->h) * (3600) + ($interval->i) * (60) + ($interval->s);
            if ($difference_time >= 1400) {
                $this->refreshToken();
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function refreshToken()
    {
        $db = Db::getInstance();
        $url = $this->_api_url;
        $path = 'auth/access_token/get';
        $params = array(
            'shop_id' => (int)$this->shop_id,
            'refresh_token' => Configuration::get('CEDSHOPEE_REFRESH_TOKEN'),
            'partner_id' => (int)$this->partner_id
        );
        $jsonBody = json_encode($params);
        $url .= $path;
        $merge_key = $this->partner_id . '/api/v2/' . $path . time();
        $sign = hash_hmac('sha256', $merge_key, $this->signature);
        $url = $url . "?partner_id=" . $this->partner_id . "&timestamp=" . time() . "&sign=" . $sign;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($response) {
            $body = json_decode($response, true);
        }
        if ($httpcode != 200) {
            return $body;
        }
        curl_close($curl);
        if (isset($body) && ($httpcode == 200)) {
            $current_time = date('Y-m-d H:i:s');
            $db->update(
                'configuration',
                array(
                    'value' => pSQL($body['expire_in']),
                    'date_upd' => pSQL($current_time)
                ),
                'name="' . 'CEDSHOPEE_EXPIRE_IN' . '"'
            );
            Configuration::updateValue('CEDSHOPEE_ACCESS_TOKEN', $body['access_token']);
            Configuration::updateValue('CEDSHOPEE_REFRESH_TOKEN', $body['refresh_token']);
            return $body;
        } else {
            return false;
        }
    }

    /**
     * Get Request
     * $params = ['file' => "", 'data' => "" ]
     * @param string $url
     * @param array $params
     * @return string|array
     */

    public function getRequest($path, $params = array())
    {
        $response = null;
        $servererror = '';
        $body = array();
        $enable = $this->isEnabled();
        if ($enable) {
            try {
                $this->checkAccessToken();
                $host = str_replace('/api/v2/', '', $this->_api_url);
                $host = str_replace('https://', '', $host);
                $url = $this->_api_url . $path;
                $merge_key = $this->partner_id . '/api/v2/' . $path . time() . $this->access_token . $this->shop_id;
                $sign = hash_hmac('sha256', $merge_key, $this->signature);
                $jsonBody = $this->createJsonParams($params, $sign);
                $url = $url . "?partner_id=" . $this->partner_id . "&timestamp=" . time() . "&sign=" . $sign .
                    "&shop_id=" . $this->shop_id . "&access_token=" . $this->access_token;
                if (isset($params['category_id'])) {
                    $url .=  "&category_id=" . $params['category_id'];
                }
                if (isset($params['order_sn'])) {
                    $url .=  "&order_sn=" . $params['order_sn'];
                }
                if (isset($params['item_id'])) {
                    $url .=  "&item_id=" . $params['item_id'];
                }
                if (isset($params['item_id_list'])) {
                    $url .=  "&item_id_list=" . $params['item_id_list'];
                }
                if (isset($params['offset'])) {
                    $url .=  "&offset=" . $params['offset'];
                }
                if (isset($params['page_size'])) {
                    $url .=  "&page_size=" . $params['page_size'];
                }
                if (isset($params['page_no'])) {
                    $url .=  "&page_no=" . $params['page_no'];
                }
                if (isset($params['status'])) {
                    $url .=  "&status=" . $params['status'];
                }
                if (isset($params['item_status'])) {
                    $url .=  "&item_status=" . $params['item_status'];
                }
                if (isset($params['time_from']) && isset($params['time_to'])) {
                    $url .=  "&time_from=" . $params['time_from'];
                    $url .=  "&time_to=" . $params['time_to'];
                    $url .=  "&time_range_field=" . $params['time_range_field'];
                }
                if (isset($params['order_sn_list'])) {
                    $url .=  "&order_sn_list=" . $params['order_sn_list'];
                    $url .=  "&response_optional_fields=item_list,recipient_address,update_time,total_amount,".
                        "split_up,shipping_carrier,ship_by_date,reverse_shipping_fee,payment_method,pay_time,".
                        "package_list,estimated_shipping_fee";
                }
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => $jsonBody,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: ' . $this->signature($url, $jsonBody),
                    ),
                ));
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($response) {
                    $body = json_decode($response, true);
                }
                if ($httpcode != 200) {
                    return $body;
                }
                if (!empty($servererror)) {
                    curl_close($ch);
                    return array('error' => 'server_error', 'msg' => $servererror);
                }
                curl_close($ch);
                if ($body && ($httpcode == 200)) {
                    return $body;
                } else {
                    return '{}';
                }
            } catch (Exception $e) {
                $this->log(
                    __METHOD__,
                    'Exception',
                    'Post Request Exception',
                    json_encode(array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )),
                    true
                );
                return array('error' => 'exception', 'msg' => $e->getMessage());
            }
        }
    }

    public function postRequest($path, $params = array())
    {
        $response = null;
        $enable = $this->isEnabled();
        if ($enable) {
            try {
                $this->checkAccessToken();
                $host = str_replace('/api/v2/', '', $this->_api_url);
                $host = str_replace('https://', '', $host);
                $url = $this->_api_url . $path;
                $merge_key = $this->partner_id . '/api/v2/' . $path . time() . $this->access_token . $this->shop_id;
                $sign = hash_hmac('sha256', $merge_key, $this->signature);
                $jsonBody = $this->createJsonParams($params, $sign);
                $url = $url . "?partner_id=" . $this->partner_id . "&timestamp=" . time() . "&sign=" . $sign .
                    "&shop_id=" . $this->shop_id . "&access_token=" . $this->access_token;
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $jsonBody,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: ' . $this->signature($url, $jsonBody),
                    ),
                ));
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($response) {
                    $body = json_decode($response, true);
                }
                if ($httpcode != 200) {
                    return $body;
                }
                curl_close($ch);
                if ($body && ($httpcode == 200)) {
                    return $body;
                } else {
                    return '{}';
                }
            } catch (Exception $e) {
                $this->log(
                    __METHOD__,
                    'Exception',
                    'Post Request Exception',
                    json_encode(array(
                        'success' => false,
                        'message' => $e->getMessage()
                    )),
                    true
                );
                return array('error' => 'exception', 'msg' => $e->getMessage());
            }
        }
    }

    /**
     * Generate an HMAC-SHA256 signature for a HTTP request
     *
     * @param UriInterface $uri
     * @param string $body
     * @return string
     */
    protected function signature($url, $body)
    {
        $data = $url . '|' . $body;
        return hash_hmac('sha256', $data, trim($this->signature));
    }

    protected function createJsonBody(array $data)
    {
        $data = array_merge(array(
            'shop_id' => (int)$this->shop_id,
            'partner_id' => (int)$this->partner_id,
        ), $data);
        return json_encode($data);
    }

    protected function createJsonParams(array $data, $sign)
    {
        $data = array_merge(array(
            'partner_id' => (int)$this->partner_id,
            'timestamp' => time(),
            'access_token' => $this->access_token,
            'shop_id' => (int)$this->shop_id,
            'sign' => $sign
        ), $data);
        return json_encode($data);
    }

    public function log($method = '', $type = '', $message = '', $response = '', $force_log = false)
    {
        if (Configuration::get('CEDSHOPEE_DEBUG_MODE') || $force_log == true) {
            $db = Db::getInstance();
            $createdAt = date('Y-m-d H:i:s');
            $db->insert(
                'cedshopee_logs',
                array(
                    'method' => pSQL($method),
                    'type' => pSQL($type),
                    'message' => pSQL($message),
                    'data' => pSQL($response, true),
                    'created_at' => pSQL($createdAt),
                )
            );
        }
    }

    public static function getShopeeAttributes()
    {
        $db = Db::getInstance();
        $result = $db->ExecuteS("SELECT `attribute_id`, `display_attribute_name` 
        FROM `" . _DB_PREFIX_ . "cedshopee_attribute` ");
        if (is_array($result) && count($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public static function getDefaultShopeeAttributes()
    {
        return array(
            'name' => array(
                'code' => 'name',
                'title' => 'Name',
                'description' => 'Name of the Product',
                'required' => true
            ),
            'description' => array(
                'code' => 'description',
                'title' => 'Description',
                'description' => 'Description of the Product',
                'required' => true
            ),
            'price' => array(
                'code' => 'price',
                'title' => 'Price',
                'description' => 'Price of the product',
                'required' => true
            ),
            'quantity' => array(
                'code' => 'quantity',
                'title' => 'Stock',
                'description' => 'Stock of the product.',
                'required' => true
            ),
            'reference' => array(
                'code' => 'reference',
                'title' => 'Item Sku',
                'description' => '',
                'required' => false
            ),
            'weight' => array(
                'code' => 'weight',
                'title' => 'Weight',
                'description' => '',
                'required' => true
            ),
            'depth' => array(
                'code' => 'depth',
                'title' => 'Package Length',
                'description' => '',
                'required' => false
            ),
            'width' => array(
                'code' => 'width',
                'title' => 'Package Width',
                'description' => '',
                'required' => false
            ),
            'height' => array(
                'code' => 'height',
                'title' => 'Package Height',
                'description' => '',
                'required' => false
            ),
            'days_to_ship' => array(
                'code' => 'days_to_ship',
                'title' => 'Days to Ship',
                'description' => '',
                'required' => false
            )
        );
    }

    public static function getSystemAttributes()
    {
        return array(
            'name' => 'Name',
            'mp_name' => 'MP Name',
            'description' => 'Description',
            'price_ext' => 'Price(tax Excl.)',
            'price_tt' => 'Price(tax Incl.)',
            'quantity' => 'Quantity',
            'reference' => 'Reference',
            'weight' => 'Weight',
            'width' => 'Width',
            'height' => 'Height',
            'depth' => 'Depth',
            'days_to_ship' => 'Days to Ship'
        );
    }

    public static function getShopeeinvoiceOption()
    {
        return array(
            'NO_INVOICES' => 'No Invoice',
            'VAT_MARGIN_SCHEME_INVOICES' => 'Vat Margin Scheme Invoices',
            'VAT_INVOICES' => 'Vat Invoice',
            'NON_VAT_INVOICES' => 'Non Vat Invoice (PL region)',
        );
    }

    public static function getShopeeVatOption()
    {
        return array(
            '0' => '0 % ',
            '5' => '5 %',
            '8' => '8 %',
            '23' => '23 %',
        );
    }

    public static function getShopeewarrantyExcludeOption()
    {
        return array(
            'False' => 'False ',
            'True' => 'True',
        );
    }

    public function getShopeeAddressListOption()
    {
        $address_list = $this->getRequest('logistics/get_address_list');
        $address_data = array();
        if (isset($address_list['response']) && !empty($address_list['response']['address_list'])) {
            foreach ($address_list['response']['address_list'] as $addressVal) {
                $address_data[$addressVal['address_id']] = $addressVal['address'].','.$addressVal['city'].','.$addressVal['state'].','.$addressVal['region'].','.$addressVal['zipcode'].','.$addressVal['zipcode'];
                if (isset($addressVal['address_type']) && !empty($addressVal['address_type'])) {
                    $type ='(';
                    foreach ($addressVal['address_type'] as $typeVal) {
                        $type .= $typeVal.',';
                    }
                    $type .= ')';
                    $address_data[$addressVal['address_id']] .= $type;
                }
            }
        }

        return $address_data;
    }

    public static function getShopeevatorigin()
    {
        return array(
            'product_source' => 'Product source ',
            'domestic' => 'domestic',
            'foreig' => 'foreig (BR region)',

        );
    }

    public static function getShopeewarrantyOption()
    {
        return array(
            'ONE_YEAR' => 'One Year',
            'TWO_YEARS' => 'Two Year',
            'OVER_TWO_YEARS' => 'More Than two year',
        );
    }


    public static function getShopeeStatus()
    {
        return array(
            array('id_shopee_status' => '1', 'name' => 'UNPAID'),
            array('id_shopee_status' => '2', 'name' => 'TO_CONFIRM_RECEIVE'),
            array('id_shopee_status' => '3', 'name' => 'READY_TO_SHIP'),
            array('id_shopee_status' => '4', 'name' => 'PROCESSED'),
            array('id_shopee_status' => '5', 'name' => 'SHIPPED'),
            array('id_shopee_status' => '6', 'name' => 'COMPLETED'),
            array('id_shopee_status' => '7', 'name' => 'IN_CANCEL'),
            array('id_shopee_status' => '8', 'name' => 'CANCELLED'),
            array('id_shopee_status' => '9', 'name' => 'INVOICE_PENDING')
        );
    }

    public static function getLogistics()
    {
        $db = Db::getInstance();
        $result = $db->ExecuteS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_logistics` WHERE `logistic_id` >= 0");
        if (is_array($result) && count($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public static function getShopeeCategories($data = array())
    {
        $db = Db::getInstance();
        if (isset($data) && !empty($data['filter_name'])) {
            $result = $db->ExecuteS("SELECT `category_id`, `category_name` 
            FROM `" . _DB_PREFIX_ . "cedshopee_category` 
            WHERE `category_name` LIKE '%" . pSQL($data['filter_name']) . "%' ORDER BY `category_name`");
        } else {
            $result = $db->ExecuteS("SELECT `category_id` FROM `" . _DB_PREFIX_ . "cedshopee_category` 
            ORDER BY `category_name`");
        }
        if (is_array($result) && count($result)) {
            return $result;
        } else {
            return array();
        }
    }
}
