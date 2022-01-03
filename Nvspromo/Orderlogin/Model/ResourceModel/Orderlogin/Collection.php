<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce (https://www.vihadigitalcommerce.com/)
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\Orderlogin\Model\ResourceModel\Orderlogin;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Nvspromo\Orderlogin\Model\Orderlogin', 'Nvspromo\Orderlogin\Model\ResourceModel\Orderlogin');
	}
}