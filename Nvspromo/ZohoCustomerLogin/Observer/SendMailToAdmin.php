<?php
 
namespace Nvspromo\ZohoCustomerLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
 
class SendMailToAdmin implements ObserverInterface
{
 
	const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_general/email';
	protected $_transportBuilder;
	protected $inlineTranslation;
	protected $scopeConfig;
	protected $storeManager;
	protected $customerCollection;
	protected $zohoData;
 	protected $_escaper;
	
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Customer $customerCollection,
		\Nvspromo\Orderlogin\Helper\Data $zohoData,
		\Magento\Framework\Escaper $escaper
    ) {
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->customerCollection = $customerCollection;
		$this->zohoData = $zohoData;
		$this->_escaper = $escaper;
	}
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$customer = $observer->getData('customer');
		$this->inlineTranslation->suspend();
		try 
		{
			$customerData = $this->customerCollection->load($customer->getId());
            $token_header = $this->zohoData->TokenHeader();
            $url_contacts = "https://www.zohoapis.com/crm/v2/Contacts/search?page=1&per_page=20&criteria=((Email:equals:".$customer->getEmail()."))";

            $result = $this->zohoData->sendCurl($url_contacts,$token_header);
            if (!empty($result['data'])) {
            	$contact_id = '';
            	foreach ($result['data'] as $key => $Contact) {
            		$contact_id = $Contact['id'];
            	}
            	$postdata = [
	                "data" => [
	                    [
	                    	"id" => $contact_id,
	                    	"Website_Login_Approved" => true,
	                        "Website_Registration" => "Yes"
	                   ]
	                ],
	                "trigger" => [
	                    "approval"
	                ]
	            ];
				$url = 'https://www.zohoapis.com/crm/v2/Contacts/upsert';
				$this->zohoData->AddUpdateData($url,$postdata,$token_header);

            } else {
            	$postdata = [
                "data" => [
                    [
                        "Last_Name"    => $customer->getLastName(),
                        "First_Name"   => $customer->getFirstName(),
                        "Account_Name" => 'NEW WEBSITE REGISTRATION',
                        "Email" 	   => $customer->getEmail(),
                        "Phone"		   => $customerData->getCustomer_telephone(),
                        "Website_Registration" => "Yes",
                        "Association_Number"   => $customerData->getAssociation_id(),
                        "Billing_Name" => $customer->getFirstName(). ' ' .$customer->getLastName()

                   ]
                ],
	                "trigger" => [
	                    "approval",
	                    "workflow",
	                    "blueprint"
	                ]
	            ];
				$url = 'https://www.zohoapis.com/crm/v2/Contacts';
				$this->zohoData->AddUpdateData($url,$postdata,$token_header);
			}

			$sender = [
				'name' => $this->_escaper->escapeHtml(date('Y-m-d H:i:s')),
				'email' => $this->_escaper->escapeHtml($customer->getEmail()),
			];
			$postObject = new \Magento\Framework\DataObject();
			$postObject->setData($sender);
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
			$transport = 
				$this->_transportBuilder
				->setTemplateIdentifier('3')
				->setTemplateOptions(
					['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
					'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
				)

				->setTemplateVars([
					'customercompany' => $customerData->getCustomer_comapany(),
					'customername' => $customer->getFirstName(). ' ' .$customer->getLastName(),
                    'customeremail' => $customer->getEmail(),
                    'customertelephone' => $customerData->getCustomer_telephone(),
                    'customerassociationid' => $customerData->getAssociation_id(),
                    'customersageid' => $customerData->getSage_id(),
                    'customerresellerid' => $customerData->getReseller_id(),
                ])
				->setFrom($sender)
				->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
				->getTransport();
			$transport->sendMessage();
			$this->inlineTranslation->resume();
		} 
		catch (\Exception $e) 
		{
			\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
		}
	
	}
 
}
?>