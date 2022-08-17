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

class CedShopeeCategory extends ObjectModel
{
    public static $definition = array(
        'table'     => 'cedshopee_category',
        'primary'   => 'id',
        'multilang' => false,
        'fields'    => array(
            'id'  => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'category_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'category_name' => array('type' => self::TYPE_STRING,  'db_type' => 'text'),
            'parent_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'has_children' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );
 
    public $id;
    public $category_id;
    public $category_name;
    public $parent_id;
    public $has_children;
}
