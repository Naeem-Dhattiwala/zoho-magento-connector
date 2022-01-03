<?php

/**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2021 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          28/12/2021
 */

namespace Nvspromo\Payorder\Controller\Index;

class Payment extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $request;

    protected $_transportBuilder;

    protected $inlineTranslation;

    protected $scopeConfig;

    protected $storeManager;

    protected $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http
     * @param \Magento\Framework\Mail\Template\TransportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nvspromo\Orderlogin\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request           = $request;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->_helper           = $helper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getPostValue();

        if ($data) {
            $card_cc        = [];
            $card_num_error = '';
            $cvv_error      = '';
            $card_name      = $data['card_name'];
            $email_address  = $data['email_address'];
            $street_address = $data['street_address_line1'].' '.$data['street_address_line2'];
            $postcode       = $data['postcode'];
            $city           = $data['city'];
            $state          = $data['state'];
            $country_code   = $data['country'];
            $card_number    = $data['card_number'];
            $card_cc        = [$data['card_number']];
            $expire_month   = $data['cc_expires_month'];
            $expire_year    = $data['cc_expires_year'];
            $cvv            = $data['cvv_no'];
            $order_id       = $data['encode_order_id'];
            $id             = $data['order_id'];
            $grand_total    = $data['grand_total'];
            $order_no       = $data['order_no'];
            $po_no          = $data['po_no'];
            $account_name   = $data['acc_name'];
            $order_shipped  = $data['order_shipped'];
            $order_notes    = $data['order_notes'];
            $cvv_pattern    = "/^[0-9]{3,4}$/";

            foreach($card_cc as $card_num) {
                if($this->validate_cc($card_num, 'all')) {
                    $card_num_error = 0;
                } else {
                    $card_num_error = 1;
                    $this->messageManager->addWarning('Invalid card number'.' '.$card_number.'.');
                    return $resultRedirect->setPath('*/'.$order_id);
                }
            }

            if (preg_match($cvv_pattern, $cvv) == 1) {
                $cvv_error = 0;
            } else {
                $cvv_error = 1;
                $this->messageManager->addWarning('Invalid security code'.' '.$cvv.'.');
                return $resultRedirect->setPath('*/'.$order_id);
            }
            if ($card_num_error == 0 && $cvv_error == 0) {
                $username  = "NVScreditcards";
                $password  = "CCPaym3nt";
                $parmlist  = "parmlist=" . urlencode("UN~$username|PSWD~$password|TERMS~Y|");
                $parmlist .= urlencode("METHOD~ProcessTranx|TRANXTYPE~Sale|");
                $parmlist .= urlencode("CC~$card_number|EXPMNTH~$expire_month|EXPYR~$expire_year|");
                $parmlist .= urlencode("AMOUNT~$grand_total|CSC~$cvv|BNAME~$card_name|INVOICE~$order_no|");
                $parmlist .= urlencode("BADDRESS~$street_address|BCITY~$city|BSTATE~$state|BZIP~$postcode|BCOUNTRY~$country_code|EMAIL~$email_address|");
                $header    = array("MIME-Version: 1.0","Content-type: application/x-www-form-urlencoded","Contenttransfer-encoding: text");

                $url = "https://paytrace.com/api/default.pay";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parmlist);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $response = curl_exec($ch);

                curl_close($ch);
                $cardResponse       = [];
                $responseArr        = explode('|', $response);
                $removeEmptyArr     = array_filter($responseArr);
                foreach ($removeEmptyArr as $cardresponsekey => $cardresponsevalue) {
                    $responseArr2  = explode('~', $cardresponsevalue);
                    if ($responseArr2[0] == 'ERROR') {
                        $this->messageManager->addError('Card Error'.' '.$responseArr2[1]);
                        return $resultRedirect->setPath('*/'.$order_id);
                    } else {
                        $cardResponse[$responseArr2[0]] = $responseArr2[1];
                    }
                }
                if ($cardResponse['APPCODE'] == '') {
                    $this->messageManager->addError('Card Error'.' '.$cardResponse['RESPONSE']);
                    return $resultRedirect->setPath('*/'.$order_id);
                } else {
                    $this->sendMail($email_address, $cardResponse['TRANSACTIONID'], $grand_total, $card_number, $cardResponse['APPMSG'], $cardResponse['AVSRESPONSE'], $cardResponse['CSCRESPONSE'],$account_name, $order_no, $po_no);
                    
                    $UpdOrderdata = $this->updateOrder($order_notes, $order_shipped, $id);

                    //$UpdInvdata = $this->updateInvoice($order_notes, $order_shipped, $po_no);

                    $this->messageManager->addSuccess('Payment Done Successfully');

                    return $resultRedirect->setPath('*/index/success'.$order_id);
                }
            } else {

                $this->messageManager->addError('Invalid Card Details');
                return $resultRedirect->setPath('*/'.$order_id);
            }
        }
    }

    public function validate_cc($ccNum, $type = 'all', $regex = null) {
        $ccNum = str_replace(array('-', ' '), '', $ccNum);
        if (mb_strlen($ccNum) < 13) {
            return false;
        }

        if ($regex !== null) {
            if (is_string($regex) && preg_match($regex, $ccNum)) {
                return true;
            }
            return false;
        }

        $cards = array(
            'all' => array(
                'amex'          => '/^3[4|7]\\d{13}$/',
                'bankcard'      => '/^56(10\\d\\d|022[1-5])\\d{10}$/',
                'diners'        => '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
                'disc'          => '/^(?:6011|650\\d)\\d{12}$/',
                'electron'      => '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
                'enroute'       => '/^2(?:014|149)\\d{11}$/',
                'jcb'           => '/^(3\\d{4}|2100|1800)\\d{11}$/',
                'maestro'       => '/^(?:5020|6\\d{3})\\d{12}$/',
                'mc'            => '/^5[1-5]\\d{14}$/',
                'solo'          => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
                'switch'        =>
                '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
                'visa'      => '/^4\\d{12}(\\d{3})?$/',
                'voyager'   => '/^8699[0-9]{11}$/',
                'visaelectron'  => '/^4(026|17500|405|508|844|91[37])/',
                'mastercard'    => '/^(5[0-5]|2[2-7])/',
                'discover'      => '/^6([045]|22)/'
            ),
            'fast' =>
            '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'
        );

        if (is_array($type)) {
            foreach ($type as $value) {
                $regex = $cards['all'][strtolower($value)];

                if (is_string($regex) && preg_match($regex, $ccNum)) {
                    return true;
                }
            }
        } elseif ($type === 'all') {
            foreach ($cards['all'] as $value) {
                $regex = $value;

                if (is_string($regex) && preg_match($regex, $ccNum)) {
                    return true;
                }
            }
        } else {
            $regex = $cards['fast'];

            if (is_string($regex) && preg_match($regex, $ccNum)) {
                return true;
            }
        }
        return false;
    }

    public function sendMail($customer_email, $transaction_id, $grand_total, $card_number, $app_msg, $avs_result, $csc_response,$customer_name, $order_no, $po_no) {

        $this->inlineTranslation->suspend();
                     
        $sender = [
            'name' => 'NVS Promo Design Inc',
            'email' => $customer_email
        ];
         
        $sentToEmail = $customer_email;
         
        $sentToName = $this->scopeConfig->getValue('trans_email/ident_general/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         
         
        $transport = $this->_transportBuilder
        ->setTemplateIdentifier('4')
        ->setTemplateOptions(
            [
                'area' => 'frontend',
                'store' => $this->storeManager->getStore()->getId()
            ]
            )
            ->setTemplateVars([
                'transaction_id'  => $transaction_id,
                'grand_total'     => $grand_total,
                'card_number'     => $card_number,
                'app_msg'         => $app_msg,
                'avs_result'      => $avs_result,
                'csc_response'    => $csc_response,
                'customer_name'   => $customer_name,
                'order_no'        => $order_no,
                'sv_po_no'        => $po_no
            ])
            ->setFromByScope($sender)
            ->addTo($sentToEmail,$sentToName)
            ->getTransport();
             
            $transport->sendMessage();
             
            $this->inlineTranslation->resume();
    }

    public function updateOrder($order_notes, $order_shipped, $id){
        
        $token_header = $this->_helper->TokenHeader();
        $today_date   = date('m/d/y');
        $sales_notes  = $order_notes."\n".$today_date.' - '.'Order Paid';
        if ($order_shipped == 1) {
           $bodydata = array('data'=>array (array("Status"=>"Delivered", "Payment_Received"=>"Yes","Paid_in_Full"=>true,"Notes"=>$sales_notes)));
        } else {
            $bodydata = array('data'=>array (array("Status"=>"Approved","Payment_Received"=>"Yes","Paid_in_Full"=>true, "Notes"=>$sales_notes)));
        }
        $bodydata = json_encode($bodydata);
        $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/".$id;
        $UpdOrder = json_decode($this->_helper->UpdateData($url, $bodydata), true);
        return $UpdOrder;
    }

    public function updateInvoice($order_notes, $order_shipped, $pwd){

        $token_header = $this->_helper->TokenHeader();
        $UpdInvoice   = [];
        $result       = '';
        $url          = "https://www.zohoapis.com/crm/v2/Invoices/search?page=1&per_page=20&criteria=(PO_Number:equals:".$pwd.")";
        $result       = $this->_helper->sendCurl($url, $token_header);
        if($result) {
            foreach ($result['data'] as $Invoicekey => $Invoicevalue) {
                $id = $Invoicevalue['id'];
            }
            $token_header = $this->_helper->TokenHeader();
            $today_date   = date('m/d/y');
            $sales_notes  = $order_notes."\n".$today_date.' - '.'Order Paid';

            if ($order_shipped == 1) {
                $bodydata = array('data'=>array (array("Status"=>"Delivered", "Payment_Received"=>"Yes","Paid_in_Full"=>true,"Notes"=>$sales_notes)));
            } else {
                $bodydata = array('data'=>array (array("Status"=>"Approved","Payment_Received"=>"Yes","Paid_in_Full"=>true, "Notes"=>$sales_notes)));
            }
            
            $bodydata   = json_encode($bodydata);
            $url        = "https://www.zohoapis.com/crm/v2/Invoices/".$id;
            $UpdInvoice = json_decode($this->_helper->UpdateData($url, $bodydata), true);
        }
        return $result;
    }
}