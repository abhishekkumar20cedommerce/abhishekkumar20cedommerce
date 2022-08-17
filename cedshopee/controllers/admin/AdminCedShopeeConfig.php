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

class AdminCedShopeeConfigController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $link = new LinkCore();
        $controller_link = $link->getAdminLink('AdminModules');
        Tools::redirectAdmin($controller_link.'&configure=cedshopee');
        parent::__construct();
    }
}
