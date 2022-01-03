<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce 
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\Orderlogin\Cron;

class Zohoauth
{

    protected $orderloginFactory;

    protected $customerSession;

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Nvspromo\Orderlogin\Model\OrderloginFactory $orderloginFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->orderloginFactory = $orderloginFactory;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        //$url = 'https://accounts.zoho.com/oauth/v2/token?refresh_token=1000.809ba955e1ce7a1d775b5eddd3f50eb0.7287250b7ed84be106ca936be255f281&client_id=1000.P8AT4HBSY4XYQGQX8E0ZNRSC74PCJH&client_secret=d1b44c534793bbc689c6c84f912ed16a7feae576a0&grant_type=refresh_token';
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://accounts.zoho.com/oauth/v2/token?refresh_token=1000.747f6cb9a71c3e77ab7b0d0d9597e117.2aaad670cc53da1c897bfd63e9e3bb85&client_id=1000.YZA0EKREE7PGHJ0FOMJS5LM4SVPH8O&client_secret=0633caa6086aa774539abb44c747d7395c90604b21&grant_type=refresh_token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HTTPHEADER => array(
            'Cookie: stk=55d9b9ec1be4099d5a423177ed187d56; _zcsr_tmp=e4a155bf-6371-4652-9968-e34cb1ae1320; b266a5bf57=a711b6da0e6cbadb5e254290f114a026; e188bc05fe=8db261d30d9c85a68e92e4f91ec8079a; iamcsr=e4a155bf-6371-4652-9968-e34cb1ae1320'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response, true); 
        $token = $result['access_token'];
        $token_header =  array(
        "Authorization: Zoho-oauthtoken ".$token
        );
        $customerSession = $this->customerSession;
        $customerSession->setAccessTokenId($token);
        $customerSession->setAccessToken($token_header);
        $AccessTokenId = $customerSession->getAccessTokenId();
        $AccessToken = $customerSession->getAccessToken();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Zoho_token.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($AccessToken);
        $json = json_encode($token_header); 
        if($AccessTokenId){
            $zohologin =  $this->orderloginFactory->create();
            $zohologinUpdate = $zohologin->load(1, 'zoho_token_id');
            $zohologinUpdate->setRefresh_token_id($token);
            $zohologinUpdate->setRefresh_token($json);
            $zohologinUpdate->save();        
        }
    }
}
