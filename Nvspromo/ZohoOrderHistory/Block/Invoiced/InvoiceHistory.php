<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          24/12/2021
 */

namespace Nvspromo\ZohoOrderHistory\Block\Invoiced;

class InvoiceHistory extends \Magento\Framework\View\Element\Template
{

    protected $orderLoginFactory;

    protected $customerSession;

    protected $urlInterface;

    protected $storeManagerInterface;

    protected $attachmentFactory;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory
     * @param array $data
     */
    public function __construct(
        \Nvspromo\Orderlogin\Model\OrderloginFactory $orderLoginFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory,
        array $data = []
    ) {
        $this->orderLoginFactory = $orderLoginFactory;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_attachmentFactory = $attachmentFactory;
        parent::__construct($context, $data);
    }
    public function sendCurl($url_code,$token_header){
        
        $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url_code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $token_header,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $result = json_decode($response, true);
        return $result;
    }
    public function getCheckLogin(){
        $customersession = $this->customerSession->create();
        return $customersession->isLoggedIn();
    }
    public function getOrders(){
        $customersession = $this->customerSession->create();
        if ($customersession->isLoggedIn()) {
            $collection = $this->orderLoginFactory->create()->getCollection();
            foreach ($collection as $results) {
                $AccessTokenId = $results['refresh_token_id'];
                $AccessToken = $results['refresh_token'];
                $token_header =  json_decode($AccessToken, true);
            }
            $email = $customersession->getCustomer()->getEmail();

            $url_Order = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?page=1&per_page=20&criteria=((EMail:equals:".$email."))";

            if ($this->sendCurl($url_Order, $token_header)) {
                $sales_order_result = $this->sendCurl($url_Order, $token_header);
            } else {
                $sales_order_result['data'] = [];
            }

            $url_Invoice = "https://www.zohoapis.com/crm/v2/Invoices/search?page=1&per_page=20&criteria=((EMail:equals:".$email."))";

            if ($this->sendCurl($url_Invoice, $token_header)) {
                $sales_invoices_result = $this->sendCurl($url_Invoice, $token_header);
            } else {
                $sales_invoices_result['data'] = [];
            }

            $sales_invoices_final_result = array_merge($sales_order_result['data'], $sales_invoices_result['data']);

            if (isset($sales_invoices_final_result)) {
                return $sales_invoices_final_result;   
            } else {
                return '';
            }
        }
        else{
            $urlInterface = $this->urlInterface;
            $customersession->setAfterAuthUrl($urlInterface->getCurrentUrl());
            $customersession->authenticate();
        }
    }
    public function getContact(){
        $customersession = $this->customerSession->create();
        if ($customersession->isLoggedIn()) {
            $collection = $this->orderLoginFactory->create()->getCollection();
            foreach ($collection as $results) {
                $AccessTokenId = $results['refresh_token_id'];
                $AccessToken = $results['refresh_token'];
                $token_header =  json_decode($AccessToken, true);
            }
            $email = $customersession->getCustomer()->getEmail();
            $url_contacts = "https://www.zohoapis.com/crm/v2/Contacts/search?page=1&per_page=20&criteria=((Email:equals:".$email."))";
            $result = $this->sendCurl($url_contacts,$token_header);
            $total_Access = '';
            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $Contacts) {
                    $total_Access = $Contacts['Website_Total_Access'];
                }
                return $total_Access;
            }
        }
    }
    public function getContactDetails(){
        $customersession = $this->customerSession->create();
        if ($customersession->isLoggedIn()) {
            $collection = $this->orderLoginFactory->create()->getCollection();
            foreach ($collection as $results) {
                $AccessTokenId = $results['refresh_token_id'];
                $AccessToken = $results['refresh_token'];
                $token_header =  json_decode($AccessToken, true);
            }
            $email = $customersession->getCustomer()->getEmail();
            $url_contacts = "https://www.zohoapis.com/crm/v2/Contacts/search?page=1&per_page=20&criteria=((Email:equals:".$email."))";
            $result = $this->sendCurl($url_contacts,$token_header);
            $Contacts_details = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $Contacts) {
                    $Contacts_details[] = $Contacts;
                }
                return $Contacts_details;
            }
        }
    }
    public function getMediaUrl(){
        $currentStore = $this->storeManagerInterface->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
    public function getBaseUrl(){
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        return $baseUrl;
    }
    public function getAttachment($order_id){
        $AttachementCollection = $this->_attachmentFactory->create()->getCollection()
                                 ->addFieldToFilter('order_id', $order_id);
        return $AttachementCollection;
    }
}

