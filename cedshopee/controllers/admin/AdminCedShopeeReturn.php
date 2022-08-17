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

class AdminCedShopeeReturnController extends ModuleAdminController
{
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->bootstrap  = true;
        $this->table      = 'cedshopee_return';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('view');
        $this->context = Context::getContext();

        $this->fields_list = array(
            'returnsn'       => array(
                'title' => 'Return ID',
                'type'  => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'ordersn'     => array(
                'title' => 'Shopee Order ID',
                'type'  => 'text',
            ),
            'reason'     => array(
                'title' => 'Reason',
                'type'  => 'text',
            ),
            'status'     => array(
                'title' => 'Status',
                'type'  => 'text',
            )
        );

        if (Tools::getIsset('method') &&
            (trim(Tools::getValue('method'))) == 'fetchReturn'
        ) {
            $this->fetchReturn();
        }

        if (Tools::getIsset('method') &&
            (trim(Tools::getValue('method'))) == 'createDispute'
        ) {
            $this->createDispute();
        }

        if (Tools::getIsset('method') &&
            (trim(Tools::getValue('method'))) == 'confirmReturn'
        ) {
            $this->confirmReturn();
        }
       
        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['fetch_return'] = array(
                'href' => self::$currentIndex . '&method=fetchReturn&token=' . $this->token,
                'desc' => $this->l('Fetch Return', null, null, false),
                'icon' => 'process-icon-download'
            );
            $this->page_header_toolbar_btn['delete_returns'] = array(
                'href' => self::$currentIndex . '&delete_returns=1&token=' . $this->token,
                'desc' => $this->l('Delete Returns', null, null, false),
                'icon' => 'process-icon-eraser'
            );
        } elseif ($this->display == 'view') {
            $this->page_header_toolbar_btn['create_dispute'] = array(
                'href' => self::$currentIndex . '&method=createDispute&token=' . $this->token,
                'desc' => $this->l('Create Dispute', null, null, false),
                'icon' => 'process-icon-new'
            );

            $this->page_header_toolbar_btn['confirm_return'] = array(
                'href' => self::$currentIndex . '&method=confirmReturn&token=' . $this->token,
                'desc' => $this->l('confirm', null, null, false),
                'icon' => 'process-icon-check'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        if (Tools::getIsset('delete_returns') && Tools::getValue('delete_returns')) {
            $res = $this->db->Execute("TRUNCATE TABLE " . _DB_PREFIX_ . "cedshopee_return");
            if ($res) {
                $this->confirmations[] = "Returns Deleted Successfully";
            } else {
                $this->errors[] = "Failed To Delete Returns";
            }
        }
        return parent::postProcess();
    }

    public function renderForm()
    {
        $link = new LinkCore();
        $redirect = $link->getAdminLink('AdminOrders');
        Tools::redirectAdmin($redirect);
    }

    public function fetchReturn()
    {
        $CedshopeeLibrary = new CedshopeeLibrary;
        $params = array('page_no' => 0, 'page_size' => 100);
        $response = $CedshopeeLibrary->getRequest('returns/get_return_list', $params);
        if (empty($response['error'])) {
            if ($response['response']['return']) {
                $returnResponse = $this->addShopeeReturns($response['response']['return']);
                if (isset($returnResponse) && $returnResponse == 1) {
                    $this->confirmations[] = 'Return fetched successfully';
                } else {
                    $this->errors[] = 'Error while fetching returns';
                }
            } else {
                $this->errors[] = 'No response from Shopee';
            }
        } elseif (!empty($response['error'])) {
            $this->errors[] = $response['error'];
        } elseif (!empty($response['message'])) {
            $this->errors[] = $response['message'];
        } else {
            $this->errors[] = 'No response from Shopee';
        }
    }

    public function addShopeeReturns($return = array())
    {
        foreach ($return as $return_data) {
            $this->db->insert(
                'cedshopee_return',
                array(
                    'reason' => pSQL($return_data['reason']),
                    'text_reason' => pSQL($return_data['text_reason']),
                    'returnsn' => pSQL($return_data['return_sn']),
                    'ordersn' => pSQL($return_data['order_sn']),
                    'return_data' => pSQL(json_encode($return_data)),
                    'status' => pSQL($return_data['status']),
                    'dispute_request' => '',
                    'dispute_response' => ''
                )
            );
        }
        return true;
    }

    public function renderView()
    {
        $return_id = Tools::getValue('id');
        if (!empty($return_id)) {
            $result = $this->db->executeS("SELECT * FROM `". _DB_PREFIX_ ."cedshopee_return` 
            WHERE `id` = '". $return_id ."' ");
            $returnData = $result[0];
            if (!empty($returnData['return_data'])) {
                $return_data = json_decode($returnData['return_data'], true);
                $returnsn = 0;
                if (Tools::getIsset($return_data['return_sn']) &&
                    !empty($return_data['return_sn'])
                ) {
                    $returnsn = $return_data['return_sn'];
                }
                $this->context->smarty->assign(array(
                    'images' => $return_data['images'],
                    'user' => $return_data['user'],
                    'item' => $return_data['user']['item'],
                    'returnsn' => $returnsn
                    ));
                unset($return_data['images']);
                unset($return_data['user']);
                unset($return_data['user']['item']);
                $this->context->smarty->assign(array('return_data'  => $return_data));
            }
            $this->context->smarty->assign('token', $this->token);
            $returnView = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ .'cedshopee/views/templates/admin/return/return_view.tpl'
            );
            parent::renderView();

            return $returnView;
        }
    }

    public static function getReturnReasons()
    {
        return array(
            'NON_RECEIPT'=> 'NON_RECEIPT',
            'OTHER'=> 'OTHER',
            'NOT_RECEIVED'=> 'NOT_RECEIVED',
            'UNKNOWN'=> 'UNKNOWN'
        );
    }

    public function createDispute()
    {
        $cedshopee = new CedshopeeLibrary;
        $return_id = Tools::getValue('id');
        $request = Tools::getAllValues();
        if (!empty($return_id)) {
            $returnsn = $this->db->getValue("SELECT `returnsn` FROM `". _DB_PREFIX_ ."cedshopee_return` 
            WHERE `id` = '". $return_id ."' ");
            if (!empty($returnsn)) {
                $url = 'returns/dispute';
                $params = array(
                    'returnsn' => $request['returnsn'],
                    'email' => $request['email'],
                    'dispute_reason' => $request['dispute_reason'],
                    'dispute_text_reason' => $request['dispute_text_reason'],
                    'image' => $request['image']
                );
                $response = $cedshopee->postRequest($url, $params);
                if (!Tools::getIsset($response['error']) && empty($response['error'])) {
                    $returnDispute = $this->saveReturnDispute($return_id, $request, $response);
                    if (isset($returnDispute) && $returnDispute == 1) {
                        $this->confirmations[] = 'Dispute returned successfully';
                    } else {
                        $this->errors[] = 'Error while creating dispute';
                    }
                } elseif (!empty($response['error'])) {
                    $this->errors[] = $response['error'];
                } elseif (!empty($response['message'])) {
                    $this->errors[] = $response['message'];
                } else {
                    $this->errors[] = 'No response from Shopee';
                }
            }
        }
        $this->context->smarty->assign(array(
            'id'  => $return_id,
            'token' => $this->token,
            'controllerUrl' => $this->context->link->getAdminLink('AdminCedShopeeReturn'),
            ));

        $returnDispute = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ .'cedshopee/views/templates/admin/return/return_dispute.tpl'
        );
        return $returnDispute;
    }

    public function saveReturnDispute($id, $request, $response)
    {
        $this->db->update(
            'cedshopee_return',
            array(
                'dispute_request' => pSQL(json_encode($request)),
                'dispute_response' => pSQL(json_encode($response['response']))
                ),
            'id='.(int)$id
        );
        return true;
    }

    public function getDisputeDetails($id)
    {
        $result = $this->db->getValue("SELECT `dispute_request` FROM `". _DB_PREFIX_ ."cedshopee_return` 
        WHERE `id` = '". (int) $id ."' ");
        $dispute_request = json_decode($result['dispute_request'], true);
        if (!empty($dispute_request)) {
            return $dispute_request;
        } else {
            return false;
        }
    }

    public function confirmReturn()
    {
        $returnsn = Tools::getValue('returnsn');
        $return_id = Tools::getValue('id');
        if (!empty($returnsn) && !empty($return_id)) {
            $CedshopeeLibrary = new CedshopeeLibrary;
            $params = array('return_sn' => $returnsn);
            $response = $CedshopeeLibrary->postRequest('returns/confirm', $params);
            $response = $response['returns'];
            if (!Tools::getIsset($response['error']) && empty($response['error'])) {
                $returnResponse = $response['response']['return_sn'];
                if (isset($returnResponse)) {
                    $this->confirmations[] = $response['response']['msg'];
                } else {
                    $this->errors[] = 'Error while confirming returns';
                }
            } elseif (!empty($response['error'])) {
                $this->errors[] = $response['error'];
            } elseif (!empty($response['message'])) {
                $this->errors[] = $response['message'];
            } else {
                $this->errors[] = 'No response from Shopee';
            }
        }
    }
}
