<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          22/12/2021
 */

namespace Nvspromo\TrackOrderView\Controller\Index;

class Edit extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $_helper;

    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Nvspromo\Orderlogin\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Nvspromo\Orderlogin\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();  
        $this->resultPage->getConfig()->getTitle()->prepend(__($this->getPageTitle()));
        return $this->resultPage;
    }
    public function getPageTitle(){
        $token_header = $this->_helper->TokenHeader();
        $po_no = "";
        $order_no = "";
        if($this->request->getParam("so_id")){
            $sales_id = $this->request->getParam("so_id");
            $sid = base64_decode($sales_id);
            $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$sid.")";
            $result = $this->_helper->sendCurl($url, $token_header);
            if(empty($result)){
                $url = "https://www.zohoapis.com/crm/v2/Invoices/search?criteria=(id:equals:".$sid.")";
                $result = $this->_helper->sendCurl($url, $token_header);
            }
            foreach ($result['data'] as $order) {
                if($order['PO_Number']){
                    $po_no = $order['PO_Number'];
                }
                if(isset($order['Order_Number'])){
                    $order_no = $order['Order_Number'];
                }
            }
            $return_variable = 'PO# ' . $po_no .','. ' ' . 'SO#'.$order_no;
            return $return_variable;
        }
        elseif($this->request->getParam("inv_id")){
            $inv_id = base64_decode($this->request->getParam("inv_id"));
            $url = "https://www.zohoapis.com/crm/v2/Invoices/search?criteria=(id:equals:".$inv_id.")";
            $result = $this->_helper->sendCurl($url, $token_header);
            if(empty($result)){
                $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$inv_id.")";
                $result = $this->_helper->sendCurl($url, $token_header);
            }
            foreach ($result['data'] as $order) {
                if($order['PO_Number']){
                    $po_no = $order['PO_Number'];
                }
                if(isset($order['id'])){
                    $order_no = $order['id'];
                }
            }
            $return_variable = 'PO# ' . $po_no .','. ' ' . 'Invoice#'.$order_no;
            return $return_variable;
        } else {
            $this->_redirect('customer/account/');
        }
    }
}

