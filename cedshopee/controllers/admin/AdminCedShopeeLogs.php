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

class AdminCedShopeeLogsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'cedshopee_logs';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('deletelog');
        $this->fields_list = array(
            'id'       => array(
                'title' => 'ID',
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'method'  => array(
                'title' => 'ACTION',
                'type'  => 'text',
                'align' => 'center',
            ),
            'type'     => array(
                'title' => 'TYPE',
                'type'  => 'text',
                'align' => 'center',
            ),
            'message' => array(
                'title' => 'MESSAGE',
                'type'  => 'text',
                'align' => 'center',
            ),
            'data' => array(
                'title' => 'RESPONSE',
                'type'  => 'text',
                'align' => 'center',
                'search' => false,
                'class' => 'fixed-width-xs',
                'callback' => 'viewLogResponse',
            ),
            'created_at' => array(
                'title' => 'CREATED AT',
                'type' => 'datetime',
                'align' => 'center',

            ),
        );

        parent::__construct();
    }
    public function displayDeleteLogLink($token = null, $id = null)
    {
        if ($token) {
            $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');
        } else {
            $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');
        }
        if (!array_key_exists('Delete', self::$cache_lang)) {
            self::$cache_lang['Delete'] = $this->l('Delete', 'Helper');
        }
        $tpl->assign(array(
            'href' => Context::getContext()->link->getAdminLink('AdminCedShopeeLogs').
                '&deletelog='.$id.'&id='.$id,
            'action' => self::$cache_lang['Delete'],
            'id' => $id
        ));
        return $tpl->fetch(
            _PS_MODULE_DIR_ . 'cedshopee/views/templates/admin/profile/deleteprofile_row_action.tpl'
        );
    }

    public function viewLogResponse($data, $rowData)
    {
        if ($data) {
            $this->context->smarty->assign(array(
                'logId' => $data
            ));
        }
        $this->context->smarty->assign(array(
        'logData' => $rowData
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ .'cedshopee/views/templates/admin/logs/log_data.tpl'
        );
    }

    public function initToolbar()
    {
        $this->toolbar_btn['export'] = array(
            'href' => self::$currentIndex.'&export'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Export')
        );
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['delete_logs'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedShopeeLogs').'&delete_logs',
                'desc' => 'Delete All Logs',
                'icon' => 'process-icon-eraser'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $parent = $this->context->smarty->fetch(_PS_MODULE_DIR_ .
            'cedshopee/views/templates/admin/logs/log_list.tpl');
        return $parent.parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::getIsset('delete_logs')) {
            $result = $this->deleteLogs();
            if (isset($result['success']) && $result['success'] == true) {
                $this->confirmations[] = $result['message'];
            } else {
                $this->errors[] = $result['message'];
            }
        }
        if (Tools::getIsset('deletelog')) {
            $result = $this->deleteLogs(Tools::getValue('id'));
            if (isset($result['success']) && $result['success'] == true) {
                $this->confirmations[] = $result['message'];
            } else {
                $this->errors[] = $result['message'];
            }
        }
        parent::postProcess();
    }

    public function deleteLogs($log_id = '')
    {
        $db = Db::getInstance();
        try {
            if (empty($log_id)) {
                $sql = "DELETE FROM  `"._DB_PREFIX_."cedshopee_logs`";
            } else {
                $sql = "DELETE FROM  `"._DB_PREFIX_."cedshopee_logs` WHERE `id` = ".(int)$log_id."";
            }
            $res = $db->execute($sql);
            if ($res) {
                return array(
                    'success' => true,
                    'message' =>  "Log(s) deleted successfully"
                );
            } else {
                return array(
                    'success' => false,
                    'message' =>  "Failed to delete Log(s)"
                );
            }
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' =>  $e->getMessage()
            );
        }
    }
}
