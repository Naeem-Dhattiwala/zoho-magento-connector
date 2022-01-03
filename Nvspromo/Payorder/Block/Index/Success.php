<?php

 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          28/12/2021
 */

namespace Nvspromo\Payorder\Block\Index;

class Success extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    protected $request;

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->request       = $request;
        parent::__construct($context, $data);
    }
     public function getBaseUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getOrderid(){
        $order_id = $this->request->getParam("so_id");
        return base64_decode($order_id);
    }
}