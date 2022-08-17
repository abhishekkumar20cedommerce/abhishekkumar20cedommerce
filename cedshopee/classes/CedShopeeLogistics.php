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

class CedShopeeLogistics extends ObjectModel
{
    public static $definition = array(
        'table'     => 'cedshopee_logistics',
        'primary'   => 'id',
        'multilang' => false,
        'fields'    => array(
            'id'  => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'logistic_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'logistic_name' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
            'has_cod' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'enabled' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'fee_type' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
            'sizes' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
            'weight_limits' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
            'item_max_dimension' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
        ),
    );
 
    public $id;
    public $logistics_id;
    public $logistics_name;
    public $has_cod;
    public $enabled;
    public $fee_type;
    public $sizes;
    public $weight_limits;
    public $item_max_dimension;
}
