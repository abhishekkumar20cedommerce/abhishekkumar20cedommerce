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

$db =  Db::getInstance();
$sql_queries = array();

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_discount` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `discount_name` text,
          `discount_id` int(11) NOT NULL,
          `start_date` datetime,
          `end_date` datetime,
          `items` longtext NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_brand_mapping` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `store_brand_id` int(15) NOT NULL,
          `store_brand` text,
          `shopee_brand_id` int(15) NOT NULL,
          `shopee_brand_name` text,
          `profile_id` int(15) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_uploaded_products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` int(11) NOT NULL,
          `shopee_profile_id` int(11) NOT NULL,
          `shopee_status` text,
          `error_message` longtext,
          `shopee_item_id` bigint(20) NOT NULL,
          `logistics` text,
          `wholesale` text,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_attribute` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `attribute_id` bigint(20) NOT NULL,
          `display_attribute_name` text,
          `is_mandatory` tinyint(1) NOT NULL,
          `attribute_type` text,
          `input_type` text,
          `options` longtext,
          `category_id` bigint(20) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_brandlist` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `is_mandatory` bigint(20) NOT NULL,
          `brands` text,
          `category_id` bigint(1) NOT NULL,
          `input_type` text,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_category` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `category_id` bigint(11) NOT NULL,
          `category_name` text,
          `parent_id` bigint(20) NOT NULL,
          `has_children` bigint(20) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_logistics` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `logistic_id` text,
          `logistic_name` text,
          `has_cod` tinyint(1) NOT NULL,
          `enabled` tinyint(1) NOT NULL,
          `fee_type` text,
          `sizes` longtext,
          `weight_limits` longtext,
          `item_max_dimension` longtext,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_order` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_place_date` text,
          `prestashop_order_id` int(11),
          `status` text,
          `order_data` text,
          `shipment_data` text,
          `shopee_order_id` text,
          `shipment_request_data` text,
          `shipment_response_data` text,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_order_error` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `shopee_order_id` text,
          `merchant_sku` varchar(255),
          `reason` text,
          `order_data` text,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_products` (
          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
          `product_id` int(11) NOT NULL,
          `shopee_status` text,
          `error_message` longtext,
          `shopee_item_id` bigint(20) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_product_variations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` int(11) NOT NULL,
          `name` text,
          `variation_sku` text,
          `status` text,
          `is_removed` text,
          `variation_id` text NOT NULL,
          `stock` int(11) NOT NULL,
          `price` float NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_profile` (
          `id` int(10) NOT NULL AUTO_INCREMENT,
          `title` text,
          `store_category` longtext,
          `shopee_categories` longtext,
          `shopee_category` longtext,
          `profile_attribute_mapping` longtext,
          `status` int(11) NOT NULL,
          `logistics` longtext,
          `wholesale` longtext,
          `default_mapping` text,
          `profile_store` text,
          `product_manufacturer` text,
          `profile_language` int(11) NOT NULL,
          `shopee_category_name` text,
           `Brand` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_profile_products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` int(11) NOT NULL,
          `shopee_profile_id` int(11) NOT NULL,
          `shopee_status` text,
          `error_message` longtext,
          `shopee_item_id` bigint(20) NOT NULL,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_return` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `reason` text,
          `text_reason` longtext,
          `returnsn` text,
          `ordersn` text,
          `return_data` longtext,
          `status` text,
          `dispute_request` longtext,
          `dispute_response` longtext,
          PRIMARY KEY (`id`)
        ) ;";

$sql_queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cedshopee_logs` (   
        `id` int(11) NOT NULL AUTO_INCREMENT,   
        `method` text NOT NULL,   
        `type` varchar(150) NOT NULL,
        `message` text NOT NULL,   
        `data` longtext NOT NULL,   
        `created_at` datetime,   
         PRIMARY KEY (`id`) 
        );";

foreach ($sql_queries as $query) {
    if ($db->execute($query) == false) {
        return false;
    }
}
