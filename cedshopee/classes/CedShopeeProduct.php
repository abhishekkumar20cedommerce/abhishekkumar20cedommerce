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

class CedShopeeProduct
{
    public $db;
    public $CedShopeeLibrary;

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->CedShopeeLibrary = new CedShopeeLibrary();
    }

    public function getAllMappedCategories()
    {
        $mapped_categories = array();

        $row = $this->db->ExecuteS("SELECT `store_category` FROM `" . _DB_PREFIX_ . "cedshopee_profile` 
        WHERE `store_category` != ''");

        if (isset($row['0']) && $row['0']) {
            foreach ($row as $value) {
                $store_categories = json_decode($value['store_category'], true);
                $mapped_categories = array_merge($mapped_categories, $store_categories);
            }
        }
        return $mapped_categories;
    }

    public function uploadProducts($product_ids)
    {
        $product_ids = array_filter($product_ids);
        $product_ids = array_unique($product_ids);
        $validation_error = array();
        if (!empty($product_ids)) {
//            $productToUpload = array();
            $success_message = array();
            $error_message = array();
            $response = array();
            $itemCount = 0;

            foreach ($product_ids as $product_id) {
                $productToUpload = array();
                if (is_numeric($product_id)) {
                    $profile_info = $this->getProfileByProductId($product_id);

                    $default_lang = isset($profile_info['0']['profile_language']) ?
                        $profile_info['0']['profile_language'] : Configuration::get('PS_LANG_DEFAULT');

                    $context = Context::getContext();
                    $context->cart = new Cart();
                    $productObject = new Product(
                        $product_id,
                        true,
                        $default_lang,
                        (int)$context->shop->id,
                        $context
                    );
                    $product = (array) $productObject;

                    if ($profile_info && !empty($product)) {
                        $shopee_category_profile = $profile_info['0']['shopee_category']; //new_mode
                        $product_info = $this->getCedShopeeMappedProductData($product_id, $profile_info, $product);

                        $category = $this->getCedShopeeCategory($product_id, $profile_info);

                        $price = $this->getCedShopeePrice($product_id, $product_info);

                        $sql = $this->db->executes("SELECT * FROM " . _DB_PREFIX_ . "stock_available 
                        WHERE `id_product` = " . (int) $product_id);
                        if ($sql && isset($sql[0])) {
                            $stock = $sql[0]['quantity'];
                        }

                        $attributes = array();
                        $attributes = $this->getCedShopeeAttribute($product_id, $profile_info, $product);

                        $db = Db::getInstance();
                        $images = $this->productImageUrls($product_id);

                        $logistics = $this->getLogistics($profile_info, $product_id);
                        $wholesales  = $this->getWholesales($profile_info, $product_id);
                        $shopee_item_id = $this->getShopeeItemId($product_id);

                        $variants = array();

                        if ($productObject->getAttributeCombinations($default_lang)) {
                            $variants = $this->isVariantProduct($product_id, $default_lang, $profile_info);
                        }

                        if (!empty($attributes)) {
                            $productToUpload['attribute_list'] =  array_values($attributes);
                        }

                        $productToUpload['category_id'] = (int) $category;

                        if (isset($product_info['name']) &&  $product_info['name']) {
                            $productToUpload['item_name'] = (string)$product_info['name'];
                        } else {
                            $validation_error[$itemCount] = 'Product ID ' . $product_id . 'Name is required Field';
                        }

                        if (Tools::strlen($productToUpload['item_name']) > 100) {
                            $productToUpload['item_name'] = Tools::substr($productToUpload['item_name'], 0, 100);
                        }

                        if (isset($product_info['description']) &&  $product_info['description']) {
                            $productToUpload['description'] =
                                $this->prepareDescriptionWoHtml($product_info['description']);
                        } else {
                            $validation_error[$itemCount] = 'Product ID ' . $product_id .
                                ' Description is required Field';
                        }

                        if (isset($productToUpload['description']) && Tools::strlen($productToUpload['description']) == 0) {
                            $value = $this->prepareDescriptionWoHtml($productToUpload['name']);
                            $productToUpload['description'] = $value;
                        } else {
                            if (isset($productToUpload['description']) &&  Tools::strlen($productToUpload['description']) > 3000) {
                                $productToUpload['description'] =
                                    Tools::substr($productToUpload['description'], 0, 3000);
                            }

                            if (isset($productToUpload['description']) && Tools::strlen($productToUpload['description']) < 25) {
                                $productToUpload['description'] = (string)$productToUpload['description'] . '......';
                            }
                        }

                        $productToUpload['original_price'] = (float)
                        number_format((float)$price, 2, '.', '');

                        $productToUpload['normal_stock'] =  (int)$stock;

                        if (isset($product['reference']) &&  $product['reference']) {
                            $productToUpload['item_sku'] = (string)$product['reference'];
                        }

                        if (isset($product_info['weight']) && $product_info['weight']) {
                            $productToUpload['weight'] = (float)$product_info['weight'];
                        }

                        if (isset($product_info['depth']) && $product_info['depth']) {
                            $productToUpload['dimension']['package_length'] = (int)$product_info['depth'];
                        } else {
                            $productToUpload['dimension']['package_length'] = (int)0;
                        }

                        if (isset($product_info['width']) && $product_info['width']) {
                            $productToUpload['dimension']['package_width'] = (int)$product_info['width'];
                        } else {
                            $productToUpload['dimension']['package_width'] = (int)0;
                        }

                        if (isset($product_info['height']) && $product_info['height']) {
                            $productToUpload['dimension']['package_height'] = (int)$product_info['height'];
                        } else {
                            $productToUpload['dimension']['package_height'] = (int)0;
                        }

                        if (isset($product_info['days_to_ship']) && $product_info['days_to_ship']) {
                            $productToUpload['days_to_ship'] = (int)$product_info['days_to_ship'];
                        }

                        if (!empty($images)) {
                            foreach ($images as $image) {
                                $shopeeImageResponse = $this->CedShopeeLibrary->uploadImageByVersion2(array('image' => $image));
                                if (isset($shopeeImageResponse) && empty($shopeeImageResponse['error'] && isset($shopeeImageResponse['response']['image_info']))) {
                                    $productToUpload['image']['image_id_list'][] = $shopeeImageResponse['response']['image_info']['image_id'];
                                } else {
                                    $validation_error[$itemCount] = 'Product ID ' . $product_id . $shopeeImageResponse['message'];
                                }

                            }

                        } else {
                            $validation_error[$itemCount] = 'Product ID ' . $product_id . 'Image is required Field';
                        }

                        if (!empty($logistics)) {
                            $productToUpload['logistic_info'] = $logistics;
                        } else {
                            $validation_error[$itemCount] = 'Product ID ' . $product_id . 'Logistics is required Field';
                        }

                        if (!empty($wholesales)) {
                            $productToUpload['wholesale'] = (array)array($wholesales);
                        }
                        $manufacture_id = $product['id_manufacturer'];

                        $Brand = $this->getmappedBrand($manufacture_id, $profile_info);

                        if (isset($Brand) && !empty($Brand)) {
                            $productToUpload['brand']['brand_id'] = (int)$Brand[0]['shopee_brand_id'];
                            if ($Brand[0]['shopee_brand_name'] == 'NoBrand') {
                                $productToUpload['brand']['original_brand_name'] = 'No Brand';
                            } else {

                                $productToUpload['brand']['original_brand_name'] = $Brand[0]['shopee_brand_name'];
                            }
                        }

                        $productToUpload['item_id'] = !empty($shopee_item_id) ? (int)$shopee_item_id : '0';

                        if (isset($variants) && is_array($variants) && !empty($variants)) {
                            $productToUpload['tier_variation'] = (array) $variants['tier_variations'];
                            $productToUpload['variations'] = (array) $variants['variations'];
                        }
                        if (isset($profile_info[0]['profile_tax_info']) && !empty($profile_info[0]['profile_tax_info'])) {
                            $tax_info  = json_decode($profile_info[0]['profile_tax_info'], true);

                            if (isset($tax_info['invoice_option']) && !empty($tax_info['invoice_option'])) {
                                $productToUpload['tax_info']['invoice_option'] = $tax_info['invoice_option'];
                            }
                            if (isset($tax_info['vat_option']) && !empty($tax_info['vat_option'])) {
                                $productToUpload['tax_info']['vat_rate'] = $tax_info['vat_option'].'%';
                            }
                            if (isset($tax_info['origin']) && !empty($tax_info['origin'])) {
                                $productToUpload['tax_info']['origin'] = $tax_info['origin'];
                            }
                            if (isset($tax_info['origin']) && !empty($tax_info['origin'])) {
                                $productToUpload['tax_info']['origin'] = $tax_info['origin'];
                            }
                            if (isset($tax_info['tax_code']) && !empty($tax_info['tax_code'])) {
                                $productToUpload['tax_info']['tax_code'] = $tax_info['tax_code'];
                            }
                            if (isset($tax_info['hs_code']) && !empty($tax_info['hs_code'])) {
                                $productToUpload['tax_info']['hs_code'] = $tax_info['hs_code'];
                            }
                        }

                        if (isset($profile_info[0]['profile_complain_policy']) && !empty($profile_info[0]['profile_complain_policy'])) {
                            $complain_info  = json_decode($profile_info[0]['profile_complain_policy'], true);
                            //echo "<pre>"; print_r($complain_info); die();
                            if (isset($complain_info['warranty_time']) && !empty($complain_info['warranty_time'])) {
                                $productToUpload['complaint_policy']['warranty_time'] = $complain_info['warranty_time'];
                            }
                            if (isset($complain_info['exclude_warranty']) && !empty($complain_info['exclude_warranty'])) {
                                if ($complain_info['exclude_warranty'] == 'True') {
                                    $complain_info['exclude_warranty'] = true;
                                } else {
                                    $complain_info['exclude_warranty'] = false;
                                }
                                $productToUpload['complaint_policy']['exclude_entrepreneur_warranty'] = $complain_info['exclude_warranty'];
                            }
                            if (isset($complain_info['address_id']) && !empty($complain_info['address_id'])) {
                                $productToUpload['complaint_policy']['complaint_address_id'] = (int)$complain_info['address_id'];
                            }
                            if (isset($complain_info['extra_info']) && !empty($complain_info['extra_info'])) {
                                $productToUpload['complaint_policy']['additional_information'] = $complain_info['extra_info'];
                            }
                        }

                        $valid = $this->validateProduct($productToUpload, $category);
                        
                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Params',
                            'Params of Product - ' . $product_id,
                            json_encode($productToUpload),
                            true
                        );

                        if (isset($valid['success']) && $valid['success']) {
                            $itemCount++;
                            if (count($productToUpload) && (count($validation_error) == 0)) {
                                if (isset($productToUpload['item_id']) && !empty($productToUpload['item_id'])) {

                                    if (
                                        isset($productToUpload['variations']) && !empty($productToUpload['variations'])
                                        && isset($productToUpload['tier_variation']) &&
                                        !empty($productToUpload['tier_variation'])
                                    ) {
                                        $variations = $productToUpload['variations'];
                                        $tier_variation = $productToUpload['tier_variation'];
                                        unset($productToUpload['variations']);
                                        unset($productToUpload['tier_variation']);
                                        $newVariationArray = array();

                                        // Get Tier Variation data
                                        $getTierVariations = $this->CedShopeeLibrary->getRequest(
                                            'product/get_model_list',
                                            array('item_id' => (int)$productToUpload['item_id'])
                                        );

                                        /*$getbasic_info = $this->CedShopeeLibrary->getRequest(
                                            'product/get_item_base_info',
                                            array('item_id_list' => (int)$productToUpload['item_id'])
                                        );
                                        $getextra_info = $this->CedShopeeLibrary->getRequest(
                                            'product/get_item_extra_info',
                                            array('item_id_list' => (int)$productToUpload['item_id'])
                                        );*/


                                        $this->CedShopeeLibrary->log(
                                            __METHOD__,
                                            'Response',
                                            'Tier Variation Response',
                                            json_encode($getTierVariations),
                                            true
                                        );

                                        /*  foreach ($variations as &$variation) {
                                              if (
                                                  isset($variation['variation_id']) &&
                                                  !empty($variation['variation_id'])
                                              ) {
                                                  $this->CedShopeeLibrary->postRequest(
                                                      'product/delete_model',
                                                      array(
                                                          'item_id' => (int)$productToUpload['item_id'],
                                                          'model_id' => (int)$variation['variation_id']

                                                      )
                                                  );
                                                  unset($variation['variation_id']);
                                              }
                                          }*/

                                        if (
                                            isset($getTierVariations['response']['tier_variation']) &&
                                            !empty($getTierVariations['response']['tier_variation'])
                                        ) {

                                            foreach ($variations as $variation) {

                                                if (isset($variation['variation_id'])) {
                                                    $updateVariationArray[] = $variation;
                                                }
                                            }

                                            if (!empty($updateVariationArray)) {
                                                $addmodel['item_id'] = (int)$productToUpload['item_id'];
                                                $addmodel['model_list'] = $updateVariationArray;
                                                $tierVariationParams = array(
                                                    'item_id' => (int)$productToUpload['item_id'],
                                                    'tier_variation' => (array)$tier_variation,
                                                );
                                                $res = $this->CedShopeeLibrary->postRequest(
                                                    'product/update_tier_variation',
                                                    $tierVariationParams
                                                );

                                                $this->CedShopeeLibrary->log(
                                                    __METHOD__,
                                                    'Response',
                                                    'Tier Variation Listing Update Response',
                                                    json_encode($res),
                                                    true
                                                );
                                                if (isset($res['error']) && $res['error']) {
                                                    if (is_array($res['message'])) {
                                                        $res['msg'] = implode(', ', $res['message']);
                                                    }
                                                    $error_message[] = 'Product ID - ' . $product_id . ' ' . $res['message'];
                                                }
                                                if (isset($res['error']) && $res['error']) {
                                                    if (is_array($res['message'])) {
                                                        $res['msg'] = implode(', ', $res['message']);
                                                    }
                                                    $error_message[] = 'Product ID - ' . $product_id . ' ' . $res['message'];
                                                }
                                                $variationParams = array(
                                                    'item_id' => (int)$productToUpload['item_id'],
                                                    'variation' => array_values($newVariationArray),
                                                );

                                                $res = $this->postRequest('product/add_model', $variationParams);
                                                if (isset($res['error']) && $res['error']) {
                                                    //    return array('success' => false, 'message' => $res['msg']);
                                                    if (is_array($res['msg'])) {
                                                        $res['msg'] = implode(', ', $res['msg']);
                                                    }
                                                    $error_message[] = 'Product ID - ' . $product_id . ' ' . $res['msg'];
                                                }
                                            }
                                        } else {

                                            foreach ($variations as &$variation) {
                                                if (
                                                    isset($variation['variation_id']) &&
                                                    !empty($variation['variation_id'])
                                                ) {
                                                    $this->CedShopeeLibrary->postRequest(
                                                       'product/delete_model',
                                                        array(
                                                            'item_id' => (int)$productToUpload['item_id'],
                                                            'model_id' => (int)$variation['variation_id']

                                                        )
                                                    );
                                                    unset($variation['variation_id']);
                                                }
                                            }

                                            $variationParamss = array(
                                                'item_id' => (int) $productToUpload['item_id'],
                                                'tier_variation' => (array) $tier_variation,
                                                'model' => (array) $variations,
                                            );
//                                            echo "<pre>"; print_r($variationParamss);
                                            /*            $variationParams = array(
                                                        "item_id"=> 21802857509,
                                                        "tier_variation"=> [
                                                        [
                                                            "option_list"=> [
                                                            [
                                                                "option"=> "iP12 Pro"
                                                            ],
                                                            [
                                                                "option"=> "iP12"
                                                            ]
                                                          ],
                                                          "name"=> "model"
                                                        ],
                                                        [
                                                            "option_list"=> [
                                                            [
                                                                "option"=> "Red"
                                                            ],
                                                            [
                                                                "option"=> "Blue"
                                                            ],
                                                            [
                                                                "option"=> "Green"
                                                            ]
                                                          ],
                                                          "name"=> "color"
                                                        ]
                                                        ],
                                                        "model"=> [
                                                        [
                                                            "original_price"=> 7.7,
                                                          "model_sku"=> "CASE-IPN-A0114",
                                                          "normal_stock"=> 10,
                                                          "tier_index"=> [
                                                            0,
                                                            0
                                                        ]
                                                        ],
                                                        [
                                                            "original_price"=> 7.77,
                                                          "model_sku"=> "CASE-IPN-A0106",
                                                          "normal_stock"=> 10,
                                                          "tier_index"=> [
                                                            0,
                                                            2
                                                        ]
                                                        ],
                                                        [
                                                            "original_price"=> 7.77,
                                                          "model_sku"=> "CASE-IPN-A0110",
                                                          "normal_stock"=> 10,
                                                          "tier_index"=> [
                                                            1,
                                                            1
                                                        ]
                                                        ]
                                                        ]
                                                        );*/

                                            //echo "<pre>"; print_r($variationParams);
                                            /*$variationParams = array(
                                                'item_id' => (int) $productToUpload['item_id'],
                                                'tier_variation' => (array) $tier_variation,
                                                'model' => (array) $variations,
                                            );*/

                                            $variation_response = $this->CedShopeeLibrary->postRequest(
                                                'product/init_tier_variation',
                                                $variationParamss
                                            );


                                            $this->CedShopeeLibrary->log(
                                                __METHOD__,
                                                'Response',
                                                'Tier Variation Initialize Response',
                                                json_encode($variation_response),
                                                true
                                            );

                                            if (isset($variation_response['error']) && $variation_response['error']) {
                                                if (is_array($variation_response['message'])) {
                                                    $variation_response['message'] =
                                                        implode(', ', $variation_response['message']);
                                                }
                                                $error_message[] =
                                                    'Product ID - ' . $product_id . ' ' . $variation_response['message'];
                                            }
                                        }
                                    }
                                    $updateimg['item_id'] = $productToUpload['item_id'];

                                    $response = $this->CedShopeeLibrary->postRequest(
                                        'product/update_item',
                                        $productToUpload
                                    );

                                    $this->CedShopeeLibrary->log(
                                        __METHOD__,
                                        'Response',
                                        'Item Update Response',
                                        json_encode($response),
                                        true
                                    );
                                } else {
                                    $variations = array();
                                    $tier_variation = array();

                                    if (
                                        isset($productToUpload['variations']) && !empty($productToUpload['variations'])
                                        && isset($productToUpload['tier_variation']) &&
                                        !empty($productToUpload['tier_variation'])
                                    ) {
                                        $variations = $productToUpload['variations'];
                                        $tier_variation = $productToUpload['tier_variation'];

                                        unset($productToUpload['variations']);
                                        unset($productToUpload['tier_variation']);
                                    }

                                    $response = $this->CedShopeeLibrary->postRequest(
                                        'product/add_item',
                                        $productToUpload
                                    );

                                    $this->CedShopeeLibrary->log(
                                        __METHOD__,
                                        'Response',
                                        'Item Add Response',
                                        json_encode($response),
                                        true
                                    );
                                    if (isset($response['response']['item_id']) && $response['response']['item_id']) {
                                        if (
                                            isset($variations) && !empty($variations)
                                            && isset($tier_variation) && !empty($tier_variation)
                                        ) {
                                            $variationParams = array(
                                                'item_id' => (int) $response['response']['item_id'],
                                                'tier_variation' => (array) $tier_variation,
                                                'model' => (array) $variations,
                                            );
                                            $variation_response = $this->CedShopeeLibrary->postRequest(
                                                'product/init_tier_variation',
                                                $variationParams
                                            );
                                            $this->CedShopeeLibrary->log(
                                                __METHOD__,
                                                'Response',
                                                'Tier Variation Initialize Response',
                                                json_encode($variation_response),
                                                true
                                            );
                                        }
                                    }
                                }

                                if (isset($response['response']['item_id']) && $response['response']['item_id']) {
                                    //                                    if (isset($response['msg']) && $response['msg']) {
                                    if (!isset($response['response']['item_status']) || empty($response['response']['item_status'])) {
                                        $response['response']['item_status'] = 'NORMAL';
                                    }

                                    $alreadyExist = $this->db->executeS("SELECT * 
                                        FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products` 
                                        WHERE product_id = '" . (int)$product_id . "'");
                                    if (isset($alreadyExist) && count($alreadyExist)) {
                                        $this->db->update(
                                            'cedshopee_uploaded_products',
                                            array(
                                                'shopee_item_id' => (int)$response['response']['item_id'],
                                                'shopee_status' => pSQL($response['response']['item_status'])
                                            ),
                                            'product_id=' . (int)$product_id
                                        );
                                    } else {
                                        $this->db->insert(
                                            'cedshopee_uploaded_products',
                                            array(
                                                'product_id' => (int)$product_id,
                                                'shopee_item_id' => (int)$response['response']['item_id'],
                                                'shopee_status' => pSQL($response['response']['item_status'])
                                            ),
                                            'product_id=' . (int)$product_id
                                        );
                                    }

                                    $variations = $this->CedShopeeLibrary->getRequest(
                                    //                                            'item/get',
                                        'product/get_model_list',
                                        array('item_id' => (int)$response['response']['item_id'])
                                    );

                                    $this->CedShopeeLibrary->log(
                                        __METHOD__,
                                        'Response',
                                        'Item at Shopee, product - ' . $product_id,
                                        json_encode($variations),
                                        true
                                    );

                                    if (
                                        isset($variations['response']['model']) &&
                                        !empty($variations['response']['model'])
                                    ) {
                                        foreach ($variations['response']['model'] as $variation) {
                                            //                                                $name = $variation['name'];
                                            //                                                $qty = $variation['stock'];
                                            //                                                $price = $variation['price'];
                                            $variation_id = $variation['model_id'];
                                            $sku = $variation['model_sku'];

                                            $product_option_value_query = $this->db->executeS(
                                                "SELECT `id`, `variation_id` 
                                                        FROM `" . _DB_PREFIX_ . "cedshopee_product_variations` 
                                                        WHERE `variation_sku` = '" . pSQL($sku) . "' 
                                                        AND `product_id` = '" . (int) $product_id . "' "
                                            );

                                            if (
                                                isset($product_option_value_query) &&
                                                count($product_option_value_query)
                                            ) {
                                                $this->db->update(
                                                    'cedshopee_product_variations',
                                                    array(
                                                        'variation_id' => pSQL($variation_id),
                                                        //                                                            'stock' => (int)$qty,
                                                        //                                                            'price' => (float)$price,
                                                        //                                                            'name' => pSQL($name)
                                                    ),
                                                    'variation_sku ="' . pSQL($sku) .
                                                    '" AND product_id="' . (int)$product_id . '"'
                                                );
                                            } else {
                                                $this->db->insert(
                                                    'cedshopee_product_variations',
                                                    array(
                                                        'variation_id' => pSQL($variation_id),
                                                        //                                                            'stock' => (int)$qty,
                                                        //                                                            'price' => (float)$price,
                                                        //                                                            'name' => pSQL($name),
                                                        'variation_sku' => pSQL($sku),
                                                        'product_id' => (int)$product_id
                                                    )
                                                );
                                            }
                                        }
                                    }
                                    $success_message[] = 'Product ID - ' . $product_id . ' ' . $response['message'];
                                    //                                    }
                                } elseif (isset($response['error']) && isset($response['message']) && $response['message']) {
                                    if (is_array($response['message'])) {
                                        $response['msg'] = implode(', ', $response['message']);

                                        //    echo '<pre>'; print_r($response['msg']); die('p');
                                    }
                                    $error_message[] = 'Product ID - ' . $product_id . ' ' . $response['message'];
                                }
                            } else {
                                if (is_array($validation_error)) {
                                    $validation_error = implode(', ', $validation_error);
                                }
                                $error_message[] = 'Product ID - ' . $product_id . ' ' . $validation_error;
                            }
                        } else {
                            if (is_array($valid['message'])) {
                                $valid['message'] = implode(', ', $valid['message']);
                            }
                            $error_message[] = 'Required Attribute are Missing for Product ID - '
                                . $product_id . ' ' . $valid['message'];
                        }
                    } else {
                        $error_message[] = 'Product ID - ' . $product_id . ' profile data is empty';
                        continue;
                    }
                }
            }
        }

        $response = array(
            'error' => implode(', ', $error_message),
            'success' => implode(', ', $success_message)
        );

        return $response;
    }

    public function getmappedBrand($manufacture_id, $profile_info)
    {
        return $this->db->ExecuteS("SELECT * FROM `" . _DB_PREFIX_ .
            "cedshopee_brand_mapping` WHERE `store_brand_id`= '" . (int) $manufacture_id .
            "' AND profile_id=" . $profile_info[0]['shopee_profile_id']);
    }


    public static function prepareDescriptionWoHtml($html)
    {
        $text = $html;

        $text = str_replace(array('</li>', '</LI>'), "\n</li>", $text);
        $text = str_replace(array('<BR', '<br'), "\n<br", $text);

        $text = strip_tags($text);

        $text = str_replace('&#39;', "'", $text);

        $text = mb_convert_encoding($text, 'HTML-ENTITIES');

        $text = str_replace('&nbsp;', ' ', $text);

        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');

        return ($text);
    }

    public function getProfileByProductId($product_id)
    {
        if ($product_id) {
            $result = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_profile` cp 
            LEFT JOIN `" . _DB_PREFIX_ . "cedshopee_profile_products` cpp on (cp.id = cpp.shopee_profile_id) 
            WHERE cpp.product_id='" . $product_id . "'");
            if (isset($result) && count($result)) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getCedShopeeMappedProductData($product_id, $profile_info, $product)
    {
        $profile_info = $profile_info['0'];
        if ($product_id && isset($profile_info['default_mapping']) && $profile_info['default_mapping']) {
            $default_mapping = json_decode($profile_info['default_mapping'], true);

            if (!empty($default_mapping)) {
                $mapped_data = array();
                foreach ($default_mapping as $key => $value) {
                    if ($key == 'price') {
                        if ($value == 'price_ext') {
                            $mapped_data[$key] = Product::getPriceStatic($product_id, false);
                        } else {
                            $mapped_data[$key] = Product::getPriceStatic($product_id, true);
                        }
                    } elseif ($key == 'days_to_ship') {
                        $mapped_data[$key] = $value;
                    } else {
                        $mapped_data[$key] = $product[$value];
                    }
                }

                return $mapped_data;
            }
        } else {
            return false;
        }
    }

    public function getCedShopeeCategory($product_id, $profile_info)
    {
        $profile_info = $profile_info['0'];
        if ($product_id) {
            $shopee_category = false;
            if (isset($profile_info['shopee_category']) && $profile_info['shopee_category']) {
                $shopee_category = $profile_info['shopee_category'];
            }
            return json_decode($shopee_category, true);
        } else {
            return false;
        }
    }

    public function getCedShopeePrice($product_id, $product = array())
    {
        $product_price = 0;
        //        if (isset($product['price']) && $product['price']) {
        $product_price = $product['price'];
        //        } else {
        //            $query_price = $this->db->executeS("SELECT `price` FROM `" . _DB_PREFIX_ . "product`
        //             WHERE `id_product` = '" . (int)$product_id . "'");
        //
        //            if (isset($query_price) && count($query_price)) {
        //                $product_price = $query_price['0']['price'];
        //            }
        //        }

        $price = (float)$product_price;

        $cedshopee_price_choice = trim(Configuration::get(
            'CEDSHOPEE_PRICE_VARIANT_TYPE'
        ));

        switch ($cedshopee_price_choice) {
            case 'default':
                $price = $price;
                break;
            case 'increase_fixed':
                $fixedIncement = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_FIXED'));
                $price = $price + $fixedIncement;
                break;
            case 'decrease_fixed':
                $fixedIncement = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_FIXED'));
                $price = $price - $fixedIncement;
                break;
            case 'increase_per':
                $percentPrice = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_PER'));
                $price = (float)($price + (($price / 100) * $percentPrice));
                break;
            case 'decrease_per':
                $percentPrice = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_PER'));
                $price = (float)($price - (($price / 100) * $percentPrice));
                break;
            default:
                return (float)$price;
        }

        return (float)$price;
    }

    public function getCedShopeeQuantity($product_id, $product = array())
    {
        $quantity = 0;
        if (isset($product['quantity']) && $product['quantity']) {
            $quantity = $product['quantity'];
        } elseif ($product_id) {
            $result = $this->db->executeS("SELECT `quantity` FROM `" . _DB_PREFIX_ . "product` 
            WHERE `id_product` = '" . $product_id . "'");
            if (isset($result) && count($result)) {
                $quantity = $result['0']['quantity'];
            } else {
                $quantity = 0;
            }
        }
        return $quantity;
    }

    public function getCedShopeeAttribute($product_id, $profile_info, $product)
    {

        $profile_info = $profile_info['0'];
        if (
            $product_id && isset($profile_info['profile_attribute_mapping']) &&
            $profile_info['profile_attribute_mapping']
        ) {

            $profile_attribute_mappings = json_decode($profile_info['profile_attribute_mapping'], true);

            $attribute_shopees = array();

            if ($profile_attribute_mappings) {
                foreach ($profile_attribute_mappings as $profile_attribute_mapping) {
                    $attribute_shopee = array();

                    if (
                        isset($profile_attribute_mapping['shopee_option']) &&
                        $profile_attribute_mapping['shopee_option']
                    ) {
                        $shopee_attribute = trim($profile_attribute_mapping['shopee_attribute']);
                        $option_id = $profile_attribute_mapping['shopee_option'];
                        $shopee_category = $profile_info['shopee_category'];
                        $option_value = $this->getShopeeOptions(
                            $shopee_attribute,
                            $shopee_category,
                            $option_id
                        );

                        $type_array = explode('-', $profile_attribute_mapping['shopee_option']);

                        if (isset($type_array['0'])) {
                            $attribute_shopee = array(
                                'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                                'attribute_value_list' => [array(
                                    'value_id' => (int)$option_id,
                                    'original_value_name' => trim($option_value['original_name']),
                                    'value_unit' => $option_value['unit']
                                )]

                            );

                        }
                    } elseif (
                        isset($profile_attribute_mapping['store_attribute']) &&
                        $profile_attribute_mapping['store_attribute']
                    ) {
                        $type_array = explode('-', $profile_attribute_mapping['store_attribute']);

                        if (isset($type_array['0']) && ($type_array['0'] == 'option')) {
                            $options = array();
                            if (isset($profile_attribute_mapping['option'])) {
                                $options = array_filter($profile_attribute_mapping['option']);
                            }
                            $option_value = $this->getProductOptions(
                                $type_array['1'],
                                $profile_info['profile_language'],
                                $options
                            );

                            $attribute_shopee = array(
                                'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                                'attribute_value_list' => [array(
                                    'value_id' => 0,
                                    'original_value_name' => $option_value,
                                    'value_unit'=>''
                                )]

                            );
                        } elseif (isset($type_array['0']) && ($type_array['0'] == 'attribute')) {
                            $attribute_value = $this->getFeaturesAttributes(
                                $product_id,
                                $type_array['1'],
                                $profile_info['profile_language']
                            );
                            if (isset($attribute_value) && !empty($attribute_value)) {
                                $attribute_shopee = array(
                                    'attribute_id' => (int)$profile_attribute_mapping['shopee_attribute'],
                                    'attribute_value_list' => [array(
                                        'value_id' => 0,
                                        'original_value_name' => $attribute_value,
                                        'value_unit' => ''
                                    )]
                                );
                            }
                        } elseif (isset($type_array['0']) && ($type_array['0'] == 'product')) {


                            $attribute_shopee = array(
                                'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                                'attribute_value_list' => [array(
                                    'value_id' => 0,
                                    'original_value_name' => strval($product[$type_array['1']]),
                                    'value_unit' =>''
                                )]
                            );
                        } else {
                            if (
                                !empty($profile_attribute_mapping['shopee_attribute']) &&
                                !empty($profile_attribute_mapping['store_attribute'])
                            ) {
                                $attribute_shopee = array(
                                    'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                                    'attribute_value_list' => [array(
                                        'value_id' => 0,
                                        'original_value_name' => $profile_attribute_mapping['store_attribute'],
                                        'value_unit' => ''
                                    )]
                                );
                            }
                        }
                    } elseif (
                        isset($profile_attribute_mapping['default_values']) &&
                        $profile_attribute_mapping['default_values']
                    ) {
                        $attribute_shopee = array(
                            'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                            'attribute_value_list' => [array(
                                'value_id' => 0,
                                'original_value_name' => $profile_attribute_mapping['default_values'],
                                'value_unit' => ''
                            )]
                        );
                    }
                    if (isset($attribute_shopee['value']) && !$attribute_shopee['value']) {
                        if (
                            isset($profile_attribute_mapping['default_values']) &&
                            $profile_attribute_mapping['default_values']
                        ) {
                            $attribute_shopee = array(
                                'attribute_id' => (int) $profile_attribute_mapping['shopee_attribute'],
                                'attribute_value_list' => [array(
                                    'value_id' => 0,
                                    'original_value_name' => $profile_attribute_mapping['default_values'],
                                    'value_unit' => ''
                                )]
                            );
                        }
                    }

                    $product_d = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products` 
                    WHERE product_id = " . (int)$product_id);
                    if (
                        !empty($product_d) && isset($product_d['0']['product_attribute']) &&
                        $product_d['0']['product_attribute']
                    ) {
                        $product_attribute_d = json_decode($product_d['0']['product_attribute'], true);
                    }

                    $attributes_id = isset($attribute_shopee['attributes_id']) ?
                        $attribute_shopee['attributes_id'] : '0';
                    if (
                        isset($product_attribute_d[$attributes_id]) &&
                        isset($product_attribute_d[$attributes_id]['shopee_attribute']) &&
                        isset($product_attribute_d[$attributes_id]['default_values']) &&
                        $product_attribute_d[$attributes_id]['default_values']
                    ) {
                        $attribute_shopee['attribute_value_list'][0]['original_value_name'] =
                            $product_attribute_d[$attribute_shopee['attributes_id']]['default_values'];
                    }

                    $attribute_shopees[] = $attribute_shopee;
                }
                $attribute_shopees = array_filter($attribute_shopees);

                return $attribute_shopees;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getProductOptions($store_attribute, $language_id, $attribute_shopee)
    {

        $product_option_data = '';
        if ($store_attribute) {
            if (is_numeric($store_attribute) && !empty($store_attribute)) {
                if (isset($attribute_shopee)) {
                    foreach ($attribute_shopee as $option_values) {
                        $product_option_value_query = $this->db->executeS(
                            "SELECT al.`id_attribute` AS option_value_id, al.`name`, a.`position` AS sort_order 
                                FROM `" . _DB_PREFIX_ . "attribute` AS a LEFT JOIN `" . _DB_PREFIX_ . "attribute_lang` AS al 
                                ON (al.id_attribute = a.id_attribute) 
                                WHERE al.`id_attribute` = '" . (int)$option_values['store_attribute_id'] . "' 
                                AND al.`id_lang` = '" . (int)$language_id . "' 
                                AND a.`id_attribute_group` = '" . (int)$store_attribute . "' "
                        );
                        if (
                            count($product_option_value_query) && isset($option_values['shopee_attribute']) &&
                            $option_values['shopee_attribute']
                        ) {
                            $product_option_data = $option_values['shopee_attribute'];
                            break;
                        }
                    }
                }
            }
        }
        return $product_option_data;
    }

    public function getFeaturesAttributes($product_id, $attribute_id, $language_id)
    {
        if ($language_id) {
            $default_lang = $language_id;
        } else {
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        $sql_db_intance = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $features = $sql_db_intance->executeS('
	        SELECT value FROM ' . _DB_PREFIX_ . 'feature_product pf
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = pf.id_feature 
	        AND fl.id_lang = ' . (int)$default_lang . ')
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl 
	        ON (fvl.id_feature_value = pf.id_feature_value 
	        AND fvl.id_lang = ' . (int)$default_lang . ')
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = pf.id_feature 
	        AND fl.id_lang = ' . (int)$default_lang . ')
	        ' . Shop::addSqlAssociation('feature', 'f') . '
	        WHERE pf.id_product = ' . (int)$product_id . ' 
	        AND fl.id_feature = "' . (int)$attribute_id . '" 
	        ORDER BY f.position ASC');

        if (isset($features['0']['value'])) {
            return $features['0']['value'];
        } else {
            return false;
        }

        //        $sql_db_intance = Db::getInstance(_PS_USE_SQL_SLAVE_);
        //        $features = $sql_db_intance->executeS('
        //	        SELECT value FROM ' . _DB_PREFIX_ . 'feature_product pf
        //	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = pf.id_feature
        //	        AND fl.id_lang = ' . (int)$default_lang . ')
        //	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl
        //	        ON (fvl.id_feature_value = pf.id_feature_value
        //	        AND fvl.id_lang = ' . (int)$default_lang . ')
        //	        LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = pf.id_feature
        //	        AND fl.id_lang = ' . (int)$default_lang . ')
        //	        ' . Shop::addSqlAssociation('feature', 'f') . '
        //	        WHERE pf.id_product = ' . (int)$product_id . '
        //	        AND fl.id_feature = "' . (int)$attribute_id . '"
        //	        ORDER BY f.position ASC');
        //        if (isset($features['0']['value'])) {
        //            return $features['0']['value'];
        //        } else {
        //            return false;
        //        }

    }

    public function getLogistics($profile_info, $product_id = null)
    {
        $logistics = array();
        $profile_logistic = array();
        $fromProductUploadTable = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products` 
        WHERE `product_id` = " . (int)$product_id);

        if (isset($profile_info[0]['logistics']) && !empty($profile_info[0]['logistics'])) {
            $profile_logistics = json_decode($profile_info[0]['logistics'], true);

            if (
                isset($profile_info['0']['logistics']) && !empty($profile_logistics) &&
                is_array($profile_logistics)
            ) {

                foreach ($profile_logistics as $key => $profile_logistic) {

                    if (isset($profile_logistic['selected']) && !empty($profile_logistic['selected'])) {
                        $result = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_logistics` 
                        WHERE `logistic_id`='" . trim($profile_logistic['logistics']) . "'");

                        if ($result && isset($result[0]['fee_type']) && ($result[0]['fee_type'] = 'CUSTOM_PRICE')) {
                            if (isset($profile_logistic['shipping_fee']) && !empty($profile_logistic['shipping_fee'])) {
                                $shippingFee = $profile_logistic['shipping_fee'];
                            } else {
                                $shippingFee = '0';
                            }
                            $logistics[] = array(
                                'logistic_id' => (int)$profile_logistic['logistics'],
                                'enabled' =>  (bool) $result[0]['enabled'],
                                'is_free' =>  (bool) $profile_logistic['is_free'],
                                'shipping_fee' => (float)$shippingFee
                            );
                        } else {
                            $logistics[] = array(
                                'logistic_id' => (int)$profile_logistic['logistics'],
                                'is_free' => (bool)$profile_logistic['is_free'],
                                'enabled' => (bool)$profile_logistic['is_free']
                            );
                        }
                        if (isset($logistics[$key]['shipping_fee']) && $logistics[$key]['shipping_fee'] == '0') {
                            unset($logistics[$key]['shipping_fee']);
                        }
                        if (isset($profile_logistic['size_selection']) && !empty($profile_logistic['size_selection'])) {
                            $logistics[$key]['size_id'] = (int)$profile_logistic['size_selection'];
                        }
                    }
                }
            }
        }

        $profile_logistic = $logistics;

        if (isset($fromProductUploadTable) && isset($fromProductUploadTable[0]) && is_array($fromProductUploadTable)) {
            if (isset($fromProductUploadTable[0]['logistics']) && !empty($fromProductUploadTable[0]['logistics'])) {
                $product_logistics = @json_decode($fromProductUploadTable[0]['logistics'], true);

                if (isset($product_logistics['0']['logistics']) && !empty($product_logistics)) {
                    $logistics = array();
                    foreach ($product_logistics as $key => $product_logistic) {
                        if (isset($product_logistic['selected']) && !empty($product_logistic['selected'])) {
                            $result = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_logistics` 
                            WHERE `logistic_id`='" . trim($product_logistic['logistics']) . "'");

                            if (
                                $result && isset($result[0]['fee_type']) && ($result[0]['fee_type'] = 'CUSTOM_PRICE') &&
                                $product_logistic
                            ) {
                                if (
                                    isset($product_logistic['shipping_fee']) &&
                                    !empty($product_logistic['shipping_fee'])
                                ) {
                                    $shippingFee = $product_logistic['shipping_fee'];
                                } else {
                                    $shippingFee = '0';
                                }
                                $logistics[] = array(
                                    'logistic_id' => (int)$product_logistic['logistics'],
                                    'enabled' =>  (bool) $result[0]['enabled'],
                                    'is_free' =>  (bool) $product_logistic['is_free'],
                                    'shipping_fee' => (float)$shippingFee
                                );
                            } else {
                                $logistics[] = array(
                                    'logistic_id' => (int)$product_logistic['logistics'],
                                    'is_free' =>  (bool) $product_logistic['is_free'],
                                    'enabled' =>  (bool) $product_logistic['is_free']
                                );
                            }

                            if (isset($logistics[$key]['shipping_fee']) && $logistics[$key]['shipping_fee'] == '0') {
                                unset($logistics[$key]['shipping_fee']);
                            }
                            if (
                                isset($product_logistic['size_selection']) &&
                                !empty($product_logistic['size_selection'])
                            ) {
                                $logistics[$key]['size_id'] = (int) $product_logistic['size_selection'];
                            }
                        }
                    }
                }
            }
        }

        if (!$logistics) {
            $logistics = $profile_logistic;
        }

        return $logistics;
    }

    public function getWholesales($profile_info, $product_id = null)
    {
        $wholesales = array();

        $uploadProductTable = $this->db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products` 
        WHERE `product_id` = " . (int)$product_id);

        if (isset($profile_info[0]['wholesale']) && !empty($profile_info[0]['wholesale'])) {
            $profile_wholesale = json_decode($profile_info[0]['wholesale'], true);
            if (!empty($profile_wholesale) && isset($profile_wholesale['wholesale_min'])) {
                $wholesales['min_count'] = (int)$profile_wholesale['wholesale_min'];
            }

            if (!empty($profile_wholesale) && isset($profile_wholesale['wholesale_max'])) {
                $wholesales['max_count'] = (int)$profile_wholesale['wholesale_max'];
            }

            if (!empty($profile_wholesale) && isset($profile_wholesale['wholesale_unit_price'])) {
                $wholesales['unit_price'] = (float)$profile_wholesale['wholesale_unit_price'];
            }
        }

        if (isset($uploadProductTable) && isset($uploadProductTable[0]) && is_array($uploadProductTable)) {
            if (isset($uploadProductTable[0]['wholesale']) && !empty($uploadProductTable[0]['wholesale'])) {
                $product_wholesale = json_decode($uploadProductTable[0]['wholesale'], true);
                $wholesales['min_count'] = (isset($product_wholesale['wholesale_min']) &&
                    !empty($product_wholesale['wholesale_min'])) ? (int)$product_wholesale['wholesale_min'] : 0;
                $wholesales['max_count'] = (isset($product_wholesale['wholesale_max']) &&
                    !empty($product_wholesale['wholesale_max'])) ? (int)$product_wholesale['wholesale_max'] : 0;
                $wholesales['unit_price'] = (isset($product_wholesale['wholesale_unit_price']) &&
                    !empty($product_wholesale['wholesale_unit_price'])) ?
                    (int)$product_wholesale['wholesale_unit_price'] : 0;
            }
        }

        return array_filter($wholesales);
    }

    public function productImageUrls($product_id = 0, $attribute_id = 0)
    {
        $db = Db::getInstance();
        if ($product_id) {
            $additionalAssets = array();
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` i LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il 
            ON (i.`id_image` = il.`id_image`)';

            if ($attribute_id) {
                $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` ai ON (i.`id_image` = ai.`id_image`)';
                $attribute_filter = ' AND ai.`id_product_attribute` = ' . (int)$attribute_id;
                $sql .= ' WHERE i.`id_product` = ' . (int)$product_id . ' AND 
                il.`id_lang` = ' . (int)$default_lang . $attribute_filter . ' ORDER BY i.`position` ASC';
            } else {
                $sql .= ' WHERE i.`id_product` = ' . (int)$product_id . ' AND 
                il.`id_lang` = ' . (int)$default_lang . ' ORDER BY i.`position` ASC';
            }

            $Execute = $db->ExecuteS($sql);

            $type = ImageType::getFormatedName('large');
            $product = new Product($product_id);
            $link = new Link;
            if (count($Execute) > 0) {
                foreach ($Execute as $image) {;
                    $img = new Image($image['id_image'], $default_lang);
                    $imagePath = $img->getImgPath();
                    $image_url = $link->getImageLink($product->link_rewrite[$default_lang], $image['id_image'], $type);
                    $image_uri_path = _PS_PROD_IMG_DIR_ . $imagePath . '-' . $type . '.' . $img->image_format;

                    $additionalAssets[] = $image_uri_path;
                    if (count($additionalAssets) == 9) {
                        break;
                    }
                }
            }
            return $additionalAssets;
        }
    }

//    public function isVariantProduct($product_id, $default_lang, $profile_info)
//    {
//        $response = array();
//
//        $context = Context::getContext();
//        $context->cart = new Cart();
//        $productObject = new Product(
//            $product_id,
//            true,
//            $default_lang,
//            (int)$context->shop->id,
//            $context
//        );
//        //$variants = $this->getAttributesResume($product_id, $default_lang);
//        $variants = $productObject->getAttributeCombinations($default_lang);
//
//        if (isset($variants) && !empty($variants)) {
//            $variations = array();
//            $tier_variations = array();
//            $optionattributes = array();
//            $options_array = array();
//            $defaultstoreAttribute = json_decode($profile_info[0]['default_mapping'], true);
//            //echo "<pre>"; print_r($variants);
//            foreach ($variants as $keys => $variant) {
//                //echo "<pre>"; print_r($variant); die();
////
////                if (isset($variant['combinations'])) {
////                    foreach ($variant['combinations'] as $combkey => $combvalue) {
//////                        if (!empty($options_array)) {
//////                            foreach ($options_array[$combkey] as $optionkey => $optionvalue) {
//////                                if ($combvalue != $combvalue) {
//////                                    $options_array[$combkey][$keys]['option'] = (string)html_entity_decode($combvalue);
//////                                }
////////                                echo "<pre>"; print_r($optionvalue);echo "<pre>"; print_r($combvalue); die;
//////                            }
//////                        } else {
////                            //$options_array[$combkey][$keys]['option'] = (string)html_entity_decode($combvalue);
////                        $optionattributes[$combkey][$keys] = (string)html_entity_decode($combvalue);
////
//////                        }
////
////                    }
////                }
//                //echo "<pre>"; print_r($variant); die();
//                /* if (isset($tier_variations) && !empty($tier_variations) && isset($tier_variations[$variant['id_attribute_group']])) {*/
//                $options_array[$keys]['option'] = (string)html_entity_decode($variant['attribute_name']);
//                $groupname = str_split($variant['group_name'], 14);
//                $tier_variations[$variant['id_attribute_group']]['name'] = $groupname[0];
//                $tier_variations[$variant['id_attribute_group']]['option_list'][$variant['id_attribute']]['option'] = (string)html_entity_decode($variant['attribute_name']);
//                $variant_image = $this->productImageUrls($product_id, $variant['id_product_attribute']);
////                    echo "<pre>"; print_r($variant_image);die();
//                if (!empty($variant_image)) {
////                    foreach ($variant_image as $image) {
//                    $shopeeVarImageResponse = $this->CedShopeeLibrary->uploadImageByVersion2(array('image' => $variant_image[0]));
//
//                    if (isset($shopeeVarImageResponse) && empty($shopeeVarImageResponse['error'] && isset($shopeeVarImageResponse['response']['image_info']))) {
//                        $tier_variations[$variant['id_attribute_group']]['option_list'][$variant['id_attribute']]['image']['image_id'] = $shopeeVarImageResponse['response']['image_info']['image_id'];
//                    }
//
////                    }
//
//                }
//
//
//                //                    echo "<pre>"; print_r($tier_variations);
//                /*  } else {
//                      //$options_array[$variant['group_name']][0] = (string)html_entity_decode($variant['attribute_name']);
//
//                      $options_array[$keys]['option'] = (string)html_entity_decode($variant['attribute_name']);
//                      $tier_variations[$variant['id_attribute_group']]['name'] = $variant['group_name'];
//                      $tier_variations[$variant['id_attribute_group']]['option_list'][]['option'] = (string)html_entity_decode($variant['attribute_name']);
//
//  //                    echo "<pre>"; print_r($tier_variations);
//                  }*/
//
//                $variation_id = $this->db->getValue("SELECT `variation_id`
//                FROM `" . _DB_PREFIX_ . "cedshopee_product_variations`
//                WHERE `variation_sku` = '" . pSQL($variant['reference']) . "'
//                AND `product_id` = '" . (int) $product_id . "' ");
////                $variation_id = $this->db->executeS("SELECT *
////                FROM `" . _DB_PREFIX_ . "cedshopee_product_variations` ");
////                echo "<pre>"; print_r($variation_id); die();
//                if ($defaultstoreAttribute['price'] == 'price_tt') {
//                    $pricevat = $productObject::getPriceStatic(
//                        $product_id,
//                        true,
//                        (int)$variant['id_product_attribute'],
//                        6,
//                        null,
//                        false,
//                        false
//                    );
//                    $price = number_format((float)$pricevat, 2, '.', '');
//                } else {
//                    $price_excl_vat = $productObject::getPriceStatic(
//                        $product_id,
//                        false,
//                        (int)$variant['id_product_attribute'],
//                        6,
//                        null,
//                        false,
//                        false
//                    );
//                    $price = number_format((float)$price_excl_vat, 2, '.', '');
//                }
//
//                $pricevat = $this->priceRule($price);
//
//                if (isset($variations[$variant['id_product_attribute']])) {
//                    $name = $variations[$variant['id_product_attribute']]['name'] . ':' . $variant['attribute_name'];
//                    if ($variant['quantity'] < 0) {
//                        $variant['quantity'] = 0;
//                    }
//                    $variations[$variant['id_product_attribute']] = array(
////                        'tier_index' => array(0,)
//                        'name' => (string)html_entity_decode(trim($name)),
//                        'normal_stock' => (int)$variant['quantity'],
//                        'original_price' => (float) $pricevat,
//                        'model_sku' => (string) $variant['reference'],
//                    );
//                    if ($variation_id) {
//                        $variations[$variant['id_product_attribute']]['variation_id'] = (int) $variation_id;
//                    }
//                } else {
//                    if ($variant['quantity'] < 0) {
//                        $variant['quantity'] = 0;
//                    }
//                    $variations[$variant['id_product_attribute']] = array(
////                        'tier_index' => array(0),
//                        'name' => (string)html_entity_decode(trim($variant['attribute_name'])),
//                        'normal_stock' => (int)$variant['quantity'],
//                        'original_price' => (float) $pricevat,
//                        'model_sku' => (string) $variant['reference'],
//                    );
//
//                    if ($variation_id) {
//                        $variations[$variant['id_product_attribute']]['variation_id'] = (int) $variation_id;
//                    }
//                }
//            }
////            echo "<pre>"; print_r($tier_variations);  echo "<pre>"; print_r($variations);
////            die();
////            $filterattributes = array();
////            foreach ($optionattributes as $optionattributeskey => $optionattributesValue) {
////                $filterattributes = array_values(array_unique($optionattributesValue));
////                foreach ($filterattributes as $filterkey =>  $filtervalues) {
////                    $tier_variations[$optionattributeskey]['option_list'][]['option'] = $filtervalues;
////                }
////                $tier_variations[$optionattributeskey]['name'] = $this->getVariantNameById($optionattributeskey);
////
////
////            }
//
//            /*            if (
//                            isset($tier_variations) && !empty($tier_variations) &&
//                            isset($variations) && !empty($variations)
//                        ) {
//                            $i = 0 ;
//                            foreach ($variations as $varkey =>  &$value) {
//
//                                $tier_index = array();
//
//                                foreach ($tier_variations as $tier_variation) {
//                                    $options = $tier_variation['option_list'];
//
//                                    foreach ($options as $key => $val) {
//
//                                        if ($value['name'] == $val['option']) {
//                                            $tier_index[] =  $i;
//            //                                $tier_index =  [$i,$i];
//            //                                $i++;
//                                        }
//                                    }
//                                }
//
//                                $value['tier_index'] = $tier_index;
//                                //$value['name'] = (string)html_entity_decode(trim($value['name']));
//                            }
//                        }*/
//            $tier_variations = array_values($tier_variations);
//            foreach ($tier_variations as $tier_key =>  $tier_value) {
//                $tier_variations[$tier_key]['option_list'] = array_values($tier_value['option_list']);
//
//            }
//
//            $variations = array_values($variations);
//            foreach($variations as $vakey => $varval) {
//
//                $tier_identity = explode(':', $varval['name']);
//                foreach ($tier_variations as $tier_variation_key => $tier_variationsValue) {
//                    if (isset($tier_variationsValue['option_list'])) {
//                        foreach ($tier_variationsValue['option_list'] as $tkey => $tval) {
//                            if (isset($tier_identity[0]) && $tier_identity[0] == $tval['option']) {
//                                $variations[$vakey]['tier_index'][$tier_variation_key] = $tkey;
//                            }
//                            if (isset($tier_identity[1]) && $tier_identity[1] == $tval['option']) {
//                                $variations[$vakey]['tier_index'][$tier_variation_key] = $tkey;
//                            }
//                        }
//                    }
////                    echo "<pre>"; print_r($tier_variationsValue['option_list']);die();
//                }
//
////                echo "<pre>"; print_r($varval); die();
//            }
////            echo "<pre>"; print_r($tier_variations); echo "<pre>"; print_r($variations); die();
//            $response = array(
//                'tier_variations' => $tier_variations,
//                'variations' => $variations
//            );
//        }
//
//        return $response;
//    }

    public function isVariantProduct($product_id, $default_lang, $profile_info)
    {
        $response = array();

        $context = Context::getContext();
        $context->cart = new Cart();
        $productObject = new Product(
            $product_id,
            true,
            $default_lang,
            (int)$context->shop->id,
            $context
        );
        //$variants = $this->getAttributesResume($product_id, $default_lang);
        $variants = $productObject->getAttributeCombinations($default_lang);

        if (isset($variants) && !empty($variants)) {
            $variations = array();
            $tier_variations = array();
            $optionattributes = array();
            $options_array = array();
            $defaultstoreAttribute = json_decode($profile_info[0]['default_mapping'], true);

            foreach ($variants as $keys => $variant) {

                $options_array[$keys]['option'] = (string)html_entity_decode($variant['attribute_name']);
                $groupname = str_split($variant['group_name'], 14);
                $tier_variations[$variant['id_attribute_group']]['name'] = $groupname[0];
                $tier_variations[$variant['id_attribute_group']]['option_list'][$variant['id_attribute']]['option'] = (string)html_entity_decode($variant['attribute_name']);
                $variant_image = $this->productImageUrls($product_id, $variant['id_product_attribute']);
                if (!empty($variant_image)) {
                    $shopeeVarImageResponse = $this->CedShopeeLibrary->uploadImageByVersion2(array('image' => $variant_image[0]));
                    if (isset($shopeeVarImageResponse) && empty($shopeeVarImageResponse['error'] && isset($shopeeVarImageResponse['response']['image_info']))) {
                        $tier_variations[$variant['id_attribute_group']]['option_list'][$variant['id_attribute']]['image']['image_id'] = $shopeeVarImageResponse['response']['image_info']['image_id'];
                    }
                }

                $variation_id = $this->db->getValue("SELECT `variation_id`
                FROM `" . _DB_PREFIX_ . "cedshopee_product_variations`
                WHERE `variation_sku` = '" . pSQL($variant['reference']) . "'
                AND `product_id` = '" . (int) $product_id . "' ");

                if ($defaultstoreAttribute['price'] == 'price_tt') {
                    $pricevat = $productObject::getPriceStatic(
                        $product_id,
                        true,
                        (int)$variant['id_product_attribute'],
                        6,
                        null,
                        false,
                        false
                    );
                    $price = number_format((float)$pricevat, 2, '.', '');
                } else {
                    $price_excl_vat = $productObject::getPriceStatic(
                        $product_id,
                        false,
                        (int)$variant['id_product_attribute'],
                        6,
                        null,
                        false,
                        false
                    );
                    $price = number_format((float)$price_excl_vat, 2, '.', '');
                }

                $pricevat = $this->priceRule($price);

                if (isset($variations[$variant['id_product_attribute']])) {
                    $name = $variations[$variant['id_product_attribute']]['name'] . '-' . $variant['attribute_name'];
                    if ($variant['quantity'] < 0) {
                        $variant['quantity'] = 0;
                    }
                    $variations[$variant['id_product_attribute']] = array(
                        'name' => (string)html_entity_decode(trim($name)),
                        'normal_stock' => (int)$variant['quantity'],
                        'original_price' => (float) $pricevat,
                        'model_sku' => (string) $variant['reference'],
                    );
                    if ($variation_id) {
                        $variations[$variant['id_product_attribute']]['variation_id'] = (int) $variation_id;
                    }
                } else {
                    if ($variant['quantity'] < 0) {
                        $variant['quantity'] = 0;
                    }
                    $variations[$variant['id_product_attribute']] = array(
                        'name' => (string)html_entity_decode(trim($variant['attribute_name'])),
                        'normal_stock' => (int)$variant['quantity'],
                        'original_price' => (float) $pricevat,
                        'model_sku' => (string) $variant['reference'],
                    );

                    if ($variation_id) {
                        $variations[$variant['id_product_attribute']]['variation_id'] = (int) $variation_id;
                    }
                }
            }

            $tier_variations = array_values($tier_variations);

            foreach ($tier_variations as $tier_key =>  $tier_value) {
                $tier_variations[$tier_key]['option_list'] = array_values($tier_value['option_list']);

            }
            asort($tier_variations);
            $tier_variations = array_values($tier_variations);

            $variations = array_values($variations);

            foreach($variations as $vakey => $varval) {
                $tier_identity = explode('-', $varval['name']);
                foreach ($tier_variations as $tier_variation_key => $tier_variationsValue) {
                    if (isset($tier_variationsValue['option_list'])) {
                        foreach ($tier_variationsValue['option_list'] as $tkey => $tval) {
                            if (isset($tier_identity[0]) && $tier_identity[0] == $tval['option']) {
                                $variations[$vakey]['tier_index'][$tier_variation_key] = $tkey;
                            }
                            if (isset($tier_identity[1]) && $tier_identity[1] == $tval['option']) {
                                $variations[$vakey]['tier_index'][$tier_variation_key] = $tkey;
                            }
                        }
                    }
                }
            }
            $response = array(
                'tier_variations' => $tier_variations,
                'variations' => $variations
            );
        }

        return $response;
    }

    public function getVariantNameById($id)
    {
        return Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'attribute_group_lang` WHERE id_attribute_group='.(int)$id);
    }

    public function getAttributesResume(
        $product_id,
        $id_lang,
        $attribute_value_separator = ' - ',
        $attribute_separator = ', '
    ) {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        $combinations = Db::getInstance()->executeS('SELECT pa.*, product_attribute_shop.*
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                WHERE pa.`id_product` = ' . (int)$product_id . '
                GROUP BY pa.`id_product_attribute`');

        if (!$combinations) {
            return false;
        }

        $product_attributes = array();
        foreach ($combinations as $combination) {
            $product_attributes[] = (int)$combination['id_product_attribute'];
        }

        $lang = Db::getInstance()->executeS('SELECT pac.id_product_attribute, GROUP_CONCAT(agl.`id_attribute_group`,
         \'' . pSQL($attribute_value_separator) . '\',al.`name` ORDER BY agl.`id_attribute_group` SEPARATOR \'' .
            pSQL($attribute_separator) . '\') as combinations ,a.id_attribute_group
                FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND
                 al.`id_lang` = ' . (int)$id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = 
                agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$id_lang . ')
                WHERE pac.id_product_attribute IN (' . implode(',', $product_attributes) . ')
                GROUP BY pac.id_product_attribute');

        foreach ($lang as $k => $row) {
            $temp = explode(',', $row['combinations']);
            $temp3 = array();
            foreach ($temp as $key => $value) {
                $temp1 = explode('-', $value);
                if (isset($temp1['1'])) {
                    $temp3[trim($temp1['0'])] = trim($temp1['1']);
                }
            }
            $combinations[$k]['combinations'] = $temp3;
        }

        //Get quantity of each variations
        foreach ($combinations as $key => $row) {
            $cache_key = $row['id_product'] . '_' . $row['id_product_attribute'] . '_quantity';

            if (!Cache::isStored($cache_key)) {
                $result = StockAvailable::getQuantityAvailableByProduct(
                    $row['id_product'],
                    $row['id_product_attribute']
                );
                Cache::store(
                    $cache_key,
                    $result
                );
                $combinations[$key]['quantity'] = $result;
            } else {
                $combinations[$key]['quantity'] = Cache::retrieve($cache_key);
            }
        }

        return $combinations;
    }

    // <<--New_mode
    public function getShopeeOptions($shopee_attribute, $shopee_category, $option_id)
    {
        $db = Db::getInstance();
        $shopee_original_name = array();
        $options_values = $db->executeS("SELECT `options` FROM `" . _DB_PREFIX_ . "cedshopee_attribute` 
             WHERE `category_id`= '" . pSQL($shopee_category) . "' AND `attribute_id`= '" . pSQL($shopee_attribute) . "'");
        $options_values = json_decode($options_values['0']['options'], true);

        if ($options_values) {
            foreach ($options_values as $options_value) {
                if ($options_value['value_id'] == $option_id) {
                    if (isset($options_value['original_value_name']) && $options_value['original_value_name']) {
                        $shopee_original_name['unit'] = $options_value['value_unit'];
                        $shopee_original_name['original_name'] = $options_value['original_value_name'];
                    }
                }
            }

            return $shopee_original_name;
        } else {
            return false;
        }
    }
    // <<--New_mode

    public function validateProduct($productToUpload, $category)
    {
        if (isset($productToUpload['attributes'])) {
            $required_attribute = array();
            $product_attribute = array();
            $Required_product_attribute = array();
            $result = $this->db->executeS("SELECT `attribute_id`, `display_attribute_name` 
            FROM `" . _DB_PREFIX_ . "cedshopee_attribute` WHERE `category_id` = '" . $category . "' AND `is_mandatory`='1'");

            if (isset($result) && count($result)) {
                foreach ($result as $row) {
                    $required_attribute[] =  $row['attribute_id'];
                    $Required_product_attribute[$row['attribute_id']] = $row['attribute_name'];
                }
            }

            foreach ($productToUpload['attributes'] as $attribute) {
                $product_attribute[] =  $attribute['attributes_id'];
            }
            $product_attribute = array_unique($product_attribute);
            $array_not_found = array_diff($required_attribute, $product_attribute);
            if (!empty($array_not_found)) {
                $name = '';
                foreach ($array_not_found as $attribute_id) {
                    if (isset($Required_product_attribute[$attribute_id])) {
                        $name .= $Required_product_attribute[$attribute_id] . ',';
                    }
                }
                $name = rtrim($name, ',');
                return array('success' => false, 'message' => $name);
            }
        }
        return array('success' => true, 'message' => $productToUpload);
    }

    public function updateInventory($product_id)
    {
        $success_message = array();
        $error_message = array();
        $result = array();
        $shopee_item_id = $this->getShopeeItemId($product_id);

        $profile_info = $this->getProfileByProductId($product_id);

        if (isset($shopee_item_id) && !empty($shopee_item_id)) {
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
            $sql = $this->db->executes("SELECT * FROM " . _DB_PREFIX_ . "stock_available 
            WHERE id_product=" . $product_id);
            if ($sql && isset($sql[0])) {
                $quantity = $sql[0]['quantity'];
            }

            $this->CedShopeeLibrary->log(
                __METHOD__,
                'itemstock test',
                'Update Inventory Response for product - ' . $product_id,
                json_encode($quantity),
                true
            );
            $variants = $this->isVariantProduct($product_id, $default_lang, $profile_info);

            if (isset($variants['variations']) && !empty($variants['variations'])) {

                foreach ($variants['variations'] as $kay => $value) {

                    if (isset($value['variation_id']) && !empty($value['variation_id'])) {
                        // echo "<pre>"; print_r($value);
                        if ($value['normal_stock'] < '0') {
                            $value['normal_stock'] = '0';
                        }
                        $stock_data = array(
                            'item_id' => (int)$shopee_item_id,
                            'stock_list' => [array(
                                'model_id' => (int)$value['variation_id'],
                                'normal_stock' => (int)$value['normal_stock']
                            )]
                        );
                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Params',
                            'Update Inventory Params for product - ' . $product_id,
                            json_encode($stock_data),
                            true
                        );

                        //$result = $this->CedShopeeLibrary->postRequest('items/update_variation_stock', $stock_data);

                        $result = $this->CedShopeeLibrary->postRequest('product/update_stock', $stock_data);

                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Update Inventory Response for product - ' . $product_id,
                            json_encode($result),
                            true
                        );
                        if (isset($result['response']) && empty($result['error'])) {
                            $this->db->update(
                                'cedshopee_product_variations',
                                array(
                                    'stock' => (int)$value['normal_stock']
                                ),
                                'product_id=' . (int)$product_id .
                                ' AND variation_id=' . (int)$value['variation_id']
                            );
                            $success_message[] = 'Variation ID - ' . $value['variation_id'] .
                                ' Quantity Updated Successfully!';
                        } elseif (isset($result['error'])) {
                            $error_message[] = $result['error'] . ' - ' . $result['message'];
                        } else {
                            $error_message[] = $result['msg'] . ' - ' . $value['error'];
                        }
                    } else {
                        if ($quantity < '0') {
                            $quantity = '0';
                        }

                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Params',
                            'Update Inventory Params for product - ' . $product_id,
                            json_encode(array('stock' => (int)$quantity, 'item_id' => (int)$shopee_item_id)),
                            true
                        );
                        $stock_data = array(
                            'item_id' => (int)$shopee_item_id,
                            'stock_list' => [array(
                                //'model_id' => (int)$value['variation_id'],
                                'normal_stock' => (int)$quantity
                            )]
                        );

                        $result = $this->CedShopeeLibrary->postRequest('product/update_stock', $stock_data);

                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Update Inventory Response for product - ' . $product_id,
                            json_encode($result),
                            true
                        );

                        if (isset($result['response']['success_list']) && !empty($result['response']['success_list'])) {
                            $success_message[] = 'Quantity Updated Successfully!';
                        } elseif (isset($result['error'])) {
                            $error_message[] = $result['error'];
                        }
                    }
                }

            } else {
                if ($quantity < '0') {
                    $quantity = '0';
                }

                $this->CedShopeeLibrary->log(
                    __METHOD__,
                    'Params',
                    'Update Inventory Params for product - ' . $product_id,
                    json_encode(array('stock' => (int)$quantity, 'item_id' => (int)$shopee_item_id)),
                    true
                );
                $this->CedShopeeLibrary->log(
                    __METHOD__,
                    'itemstock test',
                    'Update Inventory Response for product - ' . $product_id,
                    json_encode($quantity),
                    true
                );

                // $result = $this->CedShopeeLibrary->postRequest(
                //     'items/update_stock',
                //     array('stock' => (int)$quantity, 'item_id' => (int)$shopee_item_id)
                // );
                $stock_data = array(
                    'item_id' => (int)$shopee_item_id,
                    'stock_list' => [array(
                        //  'model_id' => (int)$value['variation_id'],
                        'normal_stock' => (int)$quantity
                    )]
                );

                $result = $this->CedShopeeLibrary->postRequest('product/update_stock', $stock_data);

                $this->CedShopeeLibrary->log(
                    __METHOD__,
                    'itemstock test',
                    'Update Inventory Response for product - ' . $product_id,
                    json_encode($result),
                    true
                );

                if (isset($result['message']) && $result['message']) {
                    $error_message[] = $result['message'];
                } elseif (isset($result['error'])) {
                    $error_message[] = $result['error'];
                } else {
                    $success_message[] = 'Quantity Updated Successfully!';
                }
            }
        } else {
            $error_message[] = ' not uploaded yet at Shopee!';
        }

        if (!empty($error_message)) {
            $result['error'] = 'Product ID - ' . $product_id . ' : ' . implode(" , ", $error_message);
        } elseif (!empty($success_message)) {
            $result['item'] = 'Product ID - ' . $product_id . ' : ' . implode(" , ", $success_message);
        }

        $this->CedShopeeLibrary->log(
            __METHOD__,
            'Response',
            'Update Inventory Response for product - ' . $product_id,
            json_encode($result),
            true
        );
//         echo '<pre>';
//         print_r($result);
//         die('ccc');
        return $result;
    }

    public function updatePrice($product_id)
    {
        $success_message = array();
        $error_message = array();
        $result = array();

        $shopee_item_id = $this->getShopeeItemId($product_id);
        $profile_info = $this->getProfileByProductId($product_id);

        if (isset($shopee_item_id) && !empty($shopee_item_id)) {
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            $context = Context::getContext();
            $context->cart = new Cart();
            $productObject = new Product(
                $product_id,
                true,
                $default_lang,
                (int)$context->shop->id,
                $context
            );
            $product = (array) $productObject;
            $product_info = $this->getCedShopeeMappedProductData($product_id, $profile_info, $product);

            $price = $this->getCedShopeePrice($product_id, $product_info);

            $variants = $this->isVariantProduct($product_id, $default_lang, $profile_info);

            if (isset($variants['variations']) && !empty($variants['variations'])) {
                foreach ($variants['variations'] as $value) {
                    if (isset($value['variation_id']) && !empty($value['variation_id'])) {
                        //                        $price_data = array(
                        //                            'item_id'=>(int)$shopee_item_id,
                        //                            'variation_id' => (int)$value['variation_id'],
                        //                            'price' => (float)$value['price']
                        //                        );

                        $price_data = array(
                            'item_id' => (int)$shopee_item_id,
                            'price_list' => [array(
                                'model_id' => (int)$value['variation_id'],
                                'original_price' => (float)$value['original_price']
                            )]
                        );
                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Params',
                            'Update Price Params for product - ' . $product_id,
                            json_encode($price_data),
                            true
                        );

                        //                        $result = $this->CedShopeeLibrary->postRequest(
                        //                            'items/update_variation_price',
                        //                            $price_data
                        //                        );
                        $result = $this->CedShopeeLibrary->postRequest('product/update_price', $price_data);

                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Update Price Response for product - ' . $product_id,
                            json_encode($result),
                            true
                        );

                        if (isset($result['response']) && empty($result['error'])) {
                            $this->db->update(
                                'cedshopee_product_variations',
                                array(
                                    'price' => (float) $value['original_price']
                                ),
                                'product_id=' . (int) $product_id .
                                ' AND variation_id=' . (int) $value['variation_id']
                            );
                            $success_message[] = 'Variation ID - ' . $value['variation_id'] .
                                ' Price Updated Successfully!';
                        } else {
                            $error_message[] = $result['message'] . ' - ' . $value['variation_id'];
                        }
                    } else {
                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Params',
                            'Update Price Params for product - ' . $product_id,
                            json_encode(array('price' => (float)$price, 'item_id' => (int)$shopee_item_id)),
                            true
                        );
                        $price_data = array(
                            'item_id' => (int)$shopee_item_id,
                            'price_list' => [array(
                                //                                'model_id' => (int)$value['variation_id'],
                                'original_price' => (float)$price
                            )]
                        );
                        $result = $this->CedShopeeLibrary->postRequest('product/update_price', $price_data);
                        //                        $result = $this->CedShopeeLibrary->postRequest(
                        //                            'items/update_price',
                        //                            array('price' => (float)$price, 'item_id' => (int)$shopee_item_id)
                        //                        );

                        $this->CedShopeeLibrary->log(
                            __METHOD__,
                            'Response',
                            'Update Price Response for product - ' . $product_id,
                            json_encode($result),
                            true
                        );

                        if (isset($result['message']) && $result['message']) {
                            $error_message[] = $result['nessage'];
                        } else {
                            $success_message[] = 'Price Updated Successfully!';
                        }
                    }
                }
            } else {
                $this->CedShopeeLibrary->log(
                    __METHOD__,
                    'Params',
                    'Update Price Params for product - ' . $product_id,
                    json_encode(array('price' => (float)$price, 'item_id' => (int)$shopee_item_id)),
                    true
                );
                $price_data = array(
                    'item_id' => (int)$shopee_item_id,
                    'price_list' => [array(
                        //                                'model_id' => (int)$value['variation_id'],
                        'original_price' => (float)$price
                    )]
                );

                $result = $this->CedShopeeLibrary->postRequest('product/update_price', $price_data);

                //                $result = $this->CedShopeeLibrary->postRequest(
                //                    'items/update_price',
                //                    array('price'=> (float)$price, 'item_id'=> (int)$shopee_item_id)
                //                );

                $this->CedShopeeLibrary->log(
                    __METHOD__,
                    'Response',
                    'Update Price Response for product - ' . $product_id,
                    json_encode($result),
                    true
                );

                if (isset($result['message']) && $result['message']) {
                    $error_message[] = $result['message'];
                } else {
                    $success_message[] = 'Price Updated Successfully!';
                }
            }
        } else {
            $error_message[] = ' not uploaded yet at Shopee!';
        }

        if (isset($error_message) && is_array($error_message) && $error_message) {
            $result['error'] = 'Product ID - ' . $product_id . ' : ' . implode(" , ", $error_message);
        } elseif (isset($success_message) && is_array($success_message) && $success_message) {
            $result['item'] = 'Product ID - ' . $product_id . ' : ' . implode(" , ", $success_message);
        }

        $this->CedShopeeLibrary->log(
            __METHOD__,
            'Response',
            'Update Price Response for product - ' . $product_id,
            json_encode($result),
            true
        );

        return $result;
    }

    public function getShopeeItemId($product_id = 0)
    {
        if ($product_id) {
            $shopee_item_id = $this->db->getValue("SELECT `shopee_item_id` 
            FROM `" . _DB_PREFIX_ . "cedshopee_uploaded_products` WHERE `product_id`= " . $product_id);
            return $shopee_item_id;
        }
        return null;
    }

    public function priceRule($price)
    {
        $cedshopee_price_choice = trim(Configuration::get(
            'CEDSHOPEE_PRICE_VARIANT_TYPE'
        ));

        switch ($cedshopee_price_choice) {
            case 'default':
                $price = $price;
                break;
            case 'increase_fixed':
                $fixedIncement = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_FIXED'));
                $price = $price + $fixedIncement;
                break;
            case 'decrease_fixed':
                $fixedIncement = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_FIXED'));
                $price = $price - $fixedIncement;
                break;
            case 'increase_per':
                $percentPrice = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_PER'));
                $price = (float)($price + (($price / 100) * $percentPrice));
                break;
            case 'decrease_per':
                $percentPrice = trim(Configuration::get('CEDSHOPEE_PRICE_VARIANT_PER'));
                $price = (float)($price - (($price / 100) * $percentPrice));
                break;
            default:
                return (float)$price;
        }
        return $price;
    }
}
