<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\TrackOrderView\Block\Index;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $_registry;

    protected $request;

    protected $_storeManager;

    protected $_helper;

    protected $attachmentFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Nvspromo\Orderlogin\Helper\Data $helper,
        \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_attachmentFactory = $attachmentFactory;
        parent::__construct($context, $data);
    }

    public function getOrderTrackView(){
        $token_header = $this->_helper->TokenHeader();
        if($this->request->getParam("so_id")){
            $sales_id = $this->request->getParam("so_id");
            $sid = base64_decode($sales_id);
            $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$sid.")";
            $result = $this->_helper->sendCurl($url, $token_header);
            if(empty($result)){
                $url = "https://www.zohoapis.com/crm/v2/Invoices/search?criteria=(id:equals:".$sid.")";
                $result = $this->_helper->sendCurl($url, $token_header);
            }
            return $result['data'];
        }
        if($this->request->getParam("inv_id")){
            $inv_id = base64_decode($this->request->getParam("inv_id"));
            $url = "https://www.zohoapis.com/crm/v2/Invoices/search?criteria=(id:equals:".$inv_id.")";
            $result = $this->_helper->sendCurl($url, $token_header);
            if(empty($result)){
                $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$inv_id.")";
                $result = $this->_helper->sendCurl($url, $token_header);
            }
            return $result['data'];
        }
    }
    public function getBaseUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getAttachment($order_id){
        $AttachementCollection = $this->_attachmentFactory->create()->getCollection()
                                 ->addFieldToFilter('order_id', $order_id);
        return $AttachementCollection;
    }
}

