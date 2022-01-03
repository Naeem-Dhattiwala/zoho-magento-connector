<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce (https://www.vihadigitalcommerce.com/)
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\Orderlogin\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;

class Orderlogin extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected function _construct()
	{
		$this->_init('zoho_token', 'zoho_token_id');
	}
}