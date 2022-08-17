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
 * @package   CedBol
 */

class CedShopeeSuccessModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {

        try {
            $CedShopeeLibrary = new CedShopeeLibrary;
            $json = array();
            $shop_id = '';

            if (!Tools::getValue('shop_id')) {
                $json['success'] = false;
                $json['message'] = 'Shop ID not found';
            } else {
                $shop_id = Tools::getValue('shop_id');
                $code = Tools::getValue('code');
                Configuration::updateValue('CEDSHOPEE_SHOP_ID', $shop_id);
                Configuration::updateValue('CEDSHOPEE_CODE', $code);
                $json['success'] = true;
                $json['message'] = 'Shop ID - '. $shop_id .' successfully fetched';
            }
            $CedShopeeLibrary->log(
                __METHOD__,
                'Response',
                'Shop ID - ' . $shop_id,
                json_encode($json),
                true
            );
            die(json_encode($json));
        } catch (Exception $e) {
            $CedShopeeLibrary->log(
                __METHOD__,
                'Exception',
                'Exception',
                json_encode(array('success' => false, 'message' => $e->getMessage())),
                true
            );
            die(json_encode(array(
                'success' => false,
                'message' => $e->getMessage()
            )));
        }
    }
}
