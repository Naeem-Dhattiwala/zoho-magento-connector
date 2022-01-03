<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          22/12/2021
 */

namespace Nvspromo\TrackOrderView\Model\ResourceModel\Attachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'attachment_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Nvspromo\TrackOrderView\Model\Attachment::class,
            \Nvspromo\TrackOrderView\Model\ResourceModel\Attachment::class
        );
    }
}

