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

class CedShopeeDiscount extends ObjectModel
{
    public static $definition = array(
        'table'     => 'cedshopee_discount',
        'primary'   => 'id',
        'fields'    => array(
            'id' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'discount_id' => array('type' => self::TYPE_INT, 'required' => true,  'validate' => 'isInt'),
            'discount_name' => array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isGenericName'),
            'start_date' => array('type' => self::TYPE_DATE, 'required' => false, 'validate' => 'isGenericName'),
            'end_date' => array('type' => self::TYPE_DATE, 'required' => false),
            'items' => array('type' => self::TYPE_STRING, 'required' => false)
        ),
    );

    public function getDiscountDataById($id)
    {
        $response = array(
            'discount' => array()
        );
        if (isset($id) && !empty($id)) {
            $db = Db::getInstance();
            $sql = "SELECT * FROM `"._DB_PREFIX_."cedshopee_discount` WHERE `id` = '".(int)$id."'";
            $result = $db->executeS($sql);
            if (isset($result) && count($result)) {
                $discountData = $result[0];
                $response['discount'] = array(
                    'discount_id' => $discountData['discount_id'],
                    'discount_name' => $discountData['discount_name'],
                    'start_date' => $discountData['start_date'],
                    'end_date' => $discountData['end_date'],
                    'items' => json_decode($discountData['items'], true)
                );
            }
        }
        return $response;
    }

    public static function getShopeeItems($data = array())
    {
        $db = Db::getInstance();
        if (isset($data) && !empty($data['filter_name'])) {
            $sql = "SELECT cpp.`shopee_item_id`, pl.`name` FROM `"._DB_PREFIX_."cedshopee_uploaded_products` AS cpp
            LEFT JOIN `"._DB_PREFIX_."product_lang` AS pl ON(pl.id_product = cpp.product_id)
            WHERE pl.`name` LIKE '%". $data['filter_name'] ."%' ORDER BY pl.`name` ";
            $result = $db->executeS($sql);
        } else {
            $sql = "SELECT cpp.`shopee_item_id`, pl.`name` FROM `"._DB_PREFIX_."cedshopee_products` AS cpp 
            LEFT JOIN `"._DB_PREFIX_."product_lang` AS pl ON(pl.id_product = cpp.product_id) 
            WHERE cpp.`shopee_item_id` > '0' AND pl.`id_lang` = '". (int) Context::getContext()->language->id."' 
            ORDER BY pl.`name` ";
            $result = $db->executeS($sql);
        }

        if (isset($result) && !empty($result)) {
            return $result;
        } else {
            return array();
        }
    }
}
