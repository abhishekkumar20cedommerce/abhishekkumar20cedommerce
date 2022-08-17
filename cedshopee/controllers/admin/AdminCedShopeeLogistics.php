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
require_once _PS_MODULE_DIR_ . 'cedshopee/classes/CedShopeeLogistics.php';

class AdminCedShopeeLogisticsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->bootstrap  = true;
        $this->table      = 'cedshopee_logistics';
        $this->identifier = 'logistic_id';
        $this->list_no_link = true;
        $this->bulk_actions = array(
            'remove' => array(
                'text' => 'Delete Logistics',
                'icon' => 'icon-trash'
            ),
        );
        $this->fields_list = array(
            'logistic_id'       => array(
                'title' => 'Logistics ID',
                'type'  => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'logistic_name'     => array(
                'title' => 'Logistics Name',
                'type'  => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-l',
            ),
            'enabled' => array(
                'title' => 'Status',
                'type' => 'bool',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'callback' => 'profileStatus',
                'orderby' => false
            ),
        );
        if (Tools::getIsset('method') &&
            (trim(Tools::getValue('method'))) == 'fetchLogistics'
        ) {
            $this->fetchLogistics();
        }
        parent::__construct();
    }

    public function profileStatus($value)
    {
        $this->context->smarty->assign(array('status' => (string)$value));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/profile/profile_status.tpl'
        );
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['fetch_logistics'] = array(
                'href' => self::$currentIndex . '&method=fetchLogistics&token=' . $this->token,
                'desc' => $this->l('Fetch Logistics', null, null, false),
                'icon' => 'process-icon-download'
            );
            $this->page_header_toolbar_btn['delete_logistics'] = array(
                'href' => self::$currentIndex . '&delete_logistics=1&token=' . $this->token,
                'desc' => $this->l('Delete Logistics', null, null, false),
                'icon' => 'process-icon-eraser'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        $link = new LinkCore();
        $redirect = $link->getAdminLink('AdminCedShopeeProfile').'&addcedshopee_profile';
        Tools::redirectAdmin($redirect);
    }

    public function postProcess()
    {
        if (Tools::getIsset('delete_logistics') && Tools::getValue('delete_logistics')) {
            $res = $this->db->Execute("TRUNCATE TABLE " . _DB_PREFIX_ . "cedshopee_logistics");
            if ($res) {
                $this->confirmations[] = "Logistic Deleted Successfully";
            } else {
                $this->errors[] = "Failed To Delete Logistic";
            }
        }
        return parent::postProcess();
    }

    public function fetchLogistics()
    {
        $CedShopeeLibrary = new CedShopeeLibrary;
        $response = $CedShopeeLibrary->getRequest('logistics/get_channel_list', array());
        if (empty($response['error'])) {
            if (isset($response['response']['logistics_channel_list']) &&
                !empty($response['response']['logistics_channel_list'])
            ) {
                $logiResponse = $this->addShopeeLogistic($response['response']['logistics_channel_list']);
                if (isset($logiResponse) && $logiResponse) {
                    $this->confirmations[] = 'Logistics fetched successfully';
                } else {
                    $this->errors[] = 'Error while fetching logistics';
                }
            } else {
                $this->errors[] = 'No response from Shopee';
            }
        } elseif (!empty($response['error'])) {
            $this->errors[] = $response['error'];
        } elseif (!empty($response['msg'])) {
            $this->errors[] = $response['msg'];
        } else {
            $this->errors[] = 'No response from Shopee';
        }
    }

    public function addShopeeLogistic($data = array())
    {
        $this->db->Execute("TRUNCATE TABLE " . _DB_PREFIX_ . "cedshopee_logistics");
        $flag = 0;
        foreach ($data as $logistic) {
            if (isset($logistic['logistics_channel_id']) && $logistic['logistics_channel_id']) {
                $this->db->insert(
                    'cedshopee_logistics',
                    array(
                    'logistic_id' => (int)$logistic['logistics_channel_id'],
                    'logistic_name' => pSQL($logistic['logistics_channel_name']),
                    'has_cod' => (int)$logistic['cod_enabled'],
                    'enabled' => (int)$logistic['enabled'],
                    'fee_type' => pSQL($logistic['fee_type']),
                    'sizes' => pSQL(json_encode($logistic['size_list'])),
                    'weight_limits' => pSQL(json_encode($logistic['weight_limit'])),
                    'item_max_dimension' => pSQL(json_encode($logistic['item_max_dimension']))
                    
                    )
                );
                $flag++;
            }
        }
        return $flag;
    }

    public function processBulkRemove()
    {
        $logistic_ids = Tools::getValue('cedshopee_logisticsBox');
        if (!empty($logistic_ids)) {
            foreach ($logistic_ids as $logistic_id) {
                $this->db->Execute("DELETE FROM " . _DB_PREFIX_ . "cedshopee_logistics 
                WHERE `logistic_id` = '". (int)$logistic_id ."' ");
                $this->confirmations[] = 'Logistic' . $logistic_id . 'data deleted successfully!';
            }
        } else {
            $this->errors[] = 'Please Select Logistics';
        }
    }
}
