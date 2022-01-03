<?php

 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          28/12/2021
 */

namespace Nvspromo\Payorder\Block\Index;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $countryCollectionFactory;

    protected $countryFactory;

    protected $regionFactory;

    protected $_helper;

    protected $request;

    protected $_storeManager;

    protected $attachmentFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @param \Magento\Directory\Model\CountryFactory
     * @param \Nvspromo\Orderlogin\Helper\Data
     * @param \Magento\Framework\App\Request\Http
     * @param \Magento\Directory\Model\RegionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Nvspromo\Orderlogin\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->countryFactory           = $countryFactory;
        $this->_helper                  = $helper;
        $this->request                  = $request;
        $this->regionFactory            = $regionFactory;
        $this->_attachmentFactory       = $attachmentFactory;
        $this->_storeManager            = $storeManager;
        parent::__construct($context, $data);
    }

    public function getCountryName($countryCode){
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    public function getCountryCollection()
    {
        $collection = $this->countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    public function getCountries()
    {
        $countryCollection = $this->getCountryCollection();

        $countries = [];
        foreach ($countryCollection->getData() as $country) {
            if ($country['country_id'] == "US" || $country['country_id'] == "CA" || $country['country_id'] == "MX" || $country['country_id'] =="GB") {
                $countries[$country['country_id']] = $this->getCountryName($country['country_id']);
            }
        }
        return $countries;
    }
    public function getRegion($country_id)
    {
        $region = $this->regionFactory->create()->getCollection()
                    ->addFieldToFilter('country_id', $country_id);
        return $region;
    }
    public function getOrderDetails(){
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

