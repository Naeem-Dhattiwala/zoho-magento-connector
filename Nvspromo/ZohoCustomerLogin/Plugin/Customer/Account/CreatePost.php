<?php
namespace Nvspromo\ZohoCustomerLogin\Plugin\Customer\Account;

class CreatePost
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Framework\UrlInterface $url
     */
    protected $customerSession;

    /**
     * @param \\agento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->url = $url;
        $this->customerSession = $customerSession;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\CreatePost $subject,
        $resultRedirect
    ) {        
        if(!empty($post['order_tracker_view'])){
            $this->customerSession->destroy();
            $url = "order_tracker_view/index/customerregistration?".$post['order_tracker_view'].'='.'afterregister';
            $resultRedirect->setUrl($this->url->getUrl($url));
        } else {
            $this->customerSession->destroy();
            $resultRedirect->setUrl($this->url->getUrl('customer/account/create?afterregister3321/'));
        }
        return $resultRedirect;
    }
}
?>