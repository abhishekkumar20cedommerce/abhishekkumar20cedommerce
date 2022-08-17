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

include_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLibrary.php';

class CedShopeeProfile extends ObjectModel
{
    public static $definition = array(
        'table'     => 'cedshopee_profile',
        'primary'   => 'id',
        'fields'    => array(
            'id' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'title' => array('type' => self::TYPE_STRING, 'required' => false,  'validate' => 'isGenericName'),
            'store_category' => array('type' => self::TYPE_STRING, 'required' => false, 'validate' => 'isGenericName'),
            'shopee_categories' => array('type' => self::TYPE_STRING, 'required' => false, 'validate'=>'isGenericName'),
            'shopee_category' => array('type' => self::TYPE_STRING, 'required' => false),
            'profile_attribute_mapping' => array('type' => self::TYPE_STRING, 'required' => false),
            'status' => array('type' => self::TYPE_INT, 'required' => false),
            'logistics' => array('type' => self::TYPE_STRING, 'required' => false),
            'wholesale' => array('type' => self::TYPE_STRING, 'required' => false),
            'default_mapping' => array('type' => self::TYPE_STRING, 'required' => false),
            'profile_store' => array('type' => self::TYPE_STRING, 'required' => false),
            'product_manufacturer' => array('type' => self::TYPE_STRING, 'required' => false),
            'profile_language' => array('type' => self::TYPE_INT, 'required' => false),
            'shopee_category_name' => array('type' => self::TYPE_STRING, 'required' => false),
            'Brand' => array('type' => self::TYPE_STRING, 'required' => false)
        ),
    );

    public function getProfileDataById($profileId)
    {
        $response = array(
            'general' => array(),
            'store_category' => array(),
            'profileAttributeMapping' => array(),
            'logistics' => array(),
            'wholesale' => array(),
            'defaultMapping' => array(),
            'profileStore' => array()
        );
        if (isset($profileId) && !empty($profileId)) {
            $db = Db::getInstance();
            $sql = "SELECT * FROM `"._DB_PREFIX_."cedshopee_profile` WHERE `id` = '".(int)$profileId."'";
            $result = $db->executeS($sql);
            if (isset($result) && count($result)) {
                $profileData = $result[0];
                $response['general'] = array(
                    'title' => $profileData['title'],
                    'shopee_categories' => json_decode($profileData['shopee_categories'], true),
                    'shopee_category' => $profileData['shopee_category'],
                    'status' => $profileData['status'],
                    'profile_language' => $profileData['profile_language'],
                    'shopee_category_name' => $profileData['shopee_category_name'],
                    'product_manufacturer' => json_decode($profileData['product_manufacturer'], true),
                    'profile_store' => json_decode($profileData['profile_store'], true)
                );
                $response['store_category'] = json_decode($profileData['store_category'], true);
                $response['profileAttributeMapping'] =
                    json_decode($profileData['profile_attribute_mapping'], true);
                $response['logistics'] = json_decode($profileData['logistics'], true);
                $response['wholesale'] = json_decode($profileData['wholesale'], true);
                $response['defaultMapping'] = json_decode($profileData['default_mapping'], true);
                $response['profile_tax'] = json_decode($profileData['profile_tax_info'], true);
                $response['profile_complain_policy'] = json_decode($profileData['profile_complain_policy'], true);
            }
        }
        return $response;
    }

    public function getBrandList($category_id)
    {
        $db = Db::getInstance();
        if ($category_id) {
            $sql = "SELECT * FROM `". _DB_PREFIX_ ."cedshopee_brandlist` WHERE `category_id` = ". (int)$category_id;
            $result = $db->executeS($sql);
            if (is_array($result) && isset($result['0']) && !empty($result['0'])) {
                return $result;
            } else {
                $CedShopeeLibrary = new CedShopeeLibrary;
                $params = array(
                    'category_id' => (int)$category_id,
                    'page_size' => 100,
                    'status' => 1,
                    'offset' => 0
                );
                $response = $CedShopeeLibrary->getRequest('product/get_brand_list', $params);
                if (isset($response['response']['brand_list'])) {
                    $this->addBrandList($category_id, $response['response']);
                    return $response['response']['brand_list'];
                } else {
                    return array();
                }
            }
        } else {
            return array();
        }
    }

    public function addBrandList($category_id, $data)
    {
        $db = Db::getInstance();
        $db->execute("DELETE FROM " . _DB_PREFIX_ . "cedshopee_brandlist WHERE `category_id` =".(int)$category_id);
        $db->execute("INSERT INTO " . _DB_PREFIX_ . "cedshopee_brandlist SET 
        `is_mandatory` = '" . (int)$data['is_mandatory'] . "', 
        `category_id` = '" . (int)$category_id . "', 
        `brands` = '" . pSQL(json_encode($data['brand_list'])) . "', 
        `input_type` = '" . pSQL($data['input_type']) . "'");
    }

    public function getAttributesByCategory($category_id)
    {
        if ($category_id) {
            $CedShopeeLibrary = new CedShopeeLibrary;
            $params = array('category_id' => (int)$category_id);
            $response = $CedShopeeLibrary->getRequest('product/get_attributes', $params);
            if (isset($response['response']['attribute_list'])) {
                $this->addAttributes($category_id, $response['response']['attribute_list']);
                return $response['response']['attribute_list'];
            } else {
                return $response;
            }
        } else {
            return array();
        }
    }

    public function addAttributes($category_id, $data)
    {
        $db = Db::getInstance();
        $db->execute("DELETE FROM " . _DB_PREFIX_ . "cedshopee_attribute WHERE `category_id` =".(int)$category_id);
        foreach ($data as $attribute) {
            $db->execute("INSERT INTO " . _DB_PREFIX_ . "cedshopee_attribute SET 
            `attribute_id` = '" . (int)$attribute['attribute_id'] . "', 
            `category_id` = '" . (int)$category_id . "', 
            `is_mandatory` = '" . (int)$attribute['is_mandatory'] . "', 
            `display_attribute_name` = '" . pSQL($attribute['display_attribute_name']) . "', 
            `attribute_type` = '" . pSQL($attribute['input_validation_type']) . "', 
            `input_type` = '" . pSQL($attribute['input_type']) . "', 
            `options` = '" . pSQL(json_encode($attribute['attribute_value_list'])) . "'");
        }
    }

    public function getMappedAttributes($profile_id)
    {
        $db = Db::getInstance();
        $result = $db->executeS("SELECT `profile_attribute_mapping` FROM `" . _DB_PREFIX_ . "cedshopee_profile`
        WHERE `id` = '" . (int)$profile_id . "'");
        if (is_array($result['0']) && count($result) && isset($result['0']['profile_attribute_mapping'])) {
            return json_decode($result['0']['profile_attribute_mapping'], true);
        } else {
            return array();
        }
    }

    public static function getManufacturers($data = array())
    {
        $db = Db::getInstance();
        $sql = "SELECT * FROM `". _DB_PREFIX_ ."manufacturer`";
        if (!empty($data['filter_name'])) {
            $sql .= " WHERE name LIKE '" . pSQL($data['filter_name']) . "%'";
        }
        $result = $db->executeS($sql);
        if (is_array($result) && count($result)) {
            return $result;
        }
    }

    public function storeOptions()
    {
        $db = Db::getInstance();
        $options = array();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT agl.`id_attribute_group`, agl.`name`, agl.`id_lang`, ag.`group_type`, ag.`position` 
        FROM `"._DB_PREFIX_."attribute_group_lang` AS agl LEFT JOIN `"._DB_PREFIX_."attribute_group` AS ag 
        ON (agl.id_attribute_group = ag.id_attribute_group) WHERE agl.`id_lang` = '". (int)$default_lang ."' ";
        $options = $db->executeS($sql);
        $option_value_data = array();
        $store_options = Attribute::getAttributes($default_lang, false);
        if (!empty($options)) {
            foreach ($options as $option) {
                foreach ($store_options as $value) {
                    if ($option['id_attribute_group'] == $value['id_attribute_group']) {
                        $option_value_data[$option['id_attribute_group']][] = array(
                        'id_attribute' => $value['id_attribute'],
                        'name' => $value['name']
                        );
                    }
                }
            }
        }
        return array('options' => $options, 'option_values' => $option_value_data);
    }


    public function getBrands($catId, $attribute_id, $brandName)
    {
        $db = Db::getInstance();
        $brandArray = array();
        $results = $db->executeS("SELECT * FROM `". _DB_PREFIX_ ."cedshopee_attribute` 
        WHERE `category_id` = '".$catId."' AND `attribute_id` = '".$attribute_id."'");
        foreach ($results as $res) {
            $brandArray = array_merge_recursive($brandArray, json_decode($res['options'], true));
        }
        $input = preg_quote($brandName, '~');
        $result = preg_grep('~' . $input . '~', $brandArray);
        return $result;
    }

    public function getStoreOptions($catId, $attribute_group_id, $brandName)
    {
        if ($catId) {
            $option_value_data = array();
        } else {
            $option_value_data = array();
        }
        $db = Db::getInstance();
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT al.`id_attribute`, al.`name`, a.`position` FROM `"._DB_PREFIX_."attribute` AS a 
        LEFT JOIN `"._DB_PREFIX_."attribute_lang` AS al ON (al.id_attribute = a.id_attribute) 
        WHERE a.`id_attribute_group` = '". (int)$attribute_group_id ."' AND al.`id_lang` = '". (int)$default_lang ."' 
        AND al.`name` LIKE '%". pSQL($brandName) ."%' ORDER BY a.`position` ASC";
        $option_value_query = $db->executeS($sql);

        foreach ($option_value_query as $option_value) {
            $option_value_data[] = array(
                'option_value_id' => $option_value['id_attribute'],
                'name'            => $option_value['name'],
                'sort_order'      => $option_value['position']
            );
        }
        return $option_value_data;
    }
}
