<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce (https://www.vihadigitalcommerce.com/)
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\Orderlogin\Model;

class Orderlogin extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'zoho_token';

	protected function _construct()
	{
		$this->_init('Nvspromo\Orderlogin\Model\ResourceModel\Orderlogin');
	}

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function loadByMultiple($fields)
    {
        $collection = $this->getCollection();

        foreach ($fields as $key => $value) {
            $collection = $collection->addFieldToFilter($key, $value);
        }
        
        if ($collection->getFirstItem()) {
            return $collection->getFirstItem();
        } else {
            return $this;
        }
    }
}