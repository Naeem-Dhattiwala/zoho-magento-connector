<?php
namespace Nvspromo\ZohoCustomerLogin\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
    protected $orderLoginFactory;

    protected $url;

    protected $customerSession;

    protected $messageManager;

    protected $redirect;

    protected $responseFactory;

    public function __construct(
    \Magento\Framework\App\RequestInterface $request,
     \Nvspromo\Orderlogin\Model\OrderloginFactory $orderLoginFactory,
     \Magento\Framework\UrlInterface $url,
     \Magento\Customer\Model\Session $customerSession,
     \Magento\Framework\Message\ManagerInterface $messageManager,
     \Magento\Framework\App\Response\RedirectInterface $redirect,
     \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_request = $request;
        $this->responseFactory = $responseFactory;
        $this->redirect = $redirect;
        $this->orderLoginFactory = $orderLoginFactory;
        $this->url = $url;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
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
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $controller = $observer->getControllerAction();
        if (!empty($customer->getEmail())) {
            $collection = $this->orderLoginFactory->create()->getCollection();
            foreach ($collection as $results) {
                $AccessTokenId = $results['refresh_token_id'];
                $AccessToken = $results['refresh_token'];
                $token_header =  json_decode($AccessToken, true);
            }
            $url_contacts = "https://www.zohoapis.com/crm/v2/Contacts/search?page=1&per_page=20&criteria=((Email:equals:".$customer->getEmail()."))";

            $result = $this->sendCurl($url_contacts,$token_header);
            $post = $this->_request->getPost();
            $postData = $post['order_tracker_view'];
            $postDataRegister = $post['order_tracker_view_register'];
            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $Contacts) {
                    if ($Contacts['Website_Registration'] != 'Yes') {
                        $this->customerSession->destroy();
                        $this->messageManager->addNotice( __('Not Website Registeration in Zoho'));
                        if (!empty($postData)) {
                            $posturl = 'order_tracker_view/index/customerlogin/?'.$postData;
                            $url = $this->url->getUrl($posturl).'?'.$postData;
                        } else {
                            $url = $this->url->getUrl('customer/account/login/');
                        }
                        $this->responseFactory->create()->setRedirect($url)->sendResponse();
                        die();
                    } else{
                        if($Contacts['Website_Login_Approved'] != 'true'){
                            if (!empty($postDataRegister)) {
                                 $this->customerSession->destroy();
                                if (!empty($postData)) {
                                   $posturl = "order_tracker_view/index/customerregistration";
                                    $url = $this->url->getUrl($posturl).'?' . $postData . '=' . 'afterregister/';
                                } else {
                                    $posturl = 'customer/account/create/';
                                    $url = $this->url->getUrl($posturl).'?afterregister/';
                                }
                                $this->responseFactory->create()->setRedirect($url)->sendResponse();
                                die();
                            } else {
                                 $this->customerSession->destroy();
                                if (!empty($postData)) {
                                    $this->messageManager->addNotice( __('Not Login Approved in Zoho Crm'));
                                    $posturl = 'order_tracker_view/index/customerlogin';
                                    $url = $this->url->getUrl($posturl).'?'. $postData;
                                } else {
                                    $this->messageManager->addNotice( __('Not Login Approved in Zoho Crm'));
                                    $url = $this->url->getUrl('customer/account/login/');
                                }
                                $this->responseFactory->create()->setRedirect($url)->sendResponse();
                                die();
                            }
                        }
                    }
                }
            } else {
                $this->customerSession->destroy();
                if (!empty($postData)) {
                    if (!empty($postDataRegister)) {
                       $posturl = "order_tracker_view/index/customerregistration";
                        $url = $this->url->getUrl($posturl).'?' . $postData . '=' . 'afterregister/';
                    } else {
                        $this->messageManager->addNotice( __('Not Contacts in Zoho'));
                        $posturl = 'order_tracker_view/index/customerlogin/?'.$postData;
                        $url = $this->url->getUrl($posturl).'?'.$postData;
                    }
                } else {
                    $this->customerSession->destroy();
                    if (!empty($postDataRegister)) {
                        $posturl = 'customer/account/create/?afterregister/';
                        $url = $this->url->getUrl($posturl);
                    } else {
                        $this->messageManager->addNotice( __('Not Contacts in Zoho'));
                        $url = $this->url->getUrl('customer/account/login/');
                    }
                }
                $this->responseFactory->create()->setRedirect($url)->sendResponse();
                die();
            }
        }
    }
}