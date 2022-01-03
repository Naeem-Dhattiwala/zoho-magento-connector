<?php
declare(strict_types=1);

namespace Nvspromo\Orderlogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    protected $orderLoginFactory;

    public function __construct(
        \Nvspromo\Orderlogin\Model\OrderloginFactory $orderLoginFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->orderLoginFactory = $orderLoginFactory;
        parent::__construct($context);
    }
    public function TokenHeader(){
        $collection = $this->orderLoginFactory->create()->getCollection();
        foreach ($collection as $results) {
            $AccessTokenId = $results['refresh_token_id'];
            $AccessToken = $results['refresh_token'];
            $token_header =  json_decode($AccessToken, true);
        }
        return $token_header;
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

    public function AddUpdateData($url,$postdata,$token_header){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $postdata ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $token_header);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        $error = false;
        return $result;
    }

    public function UpdateData($url,$postdata){
        $token_header = $this->TokenHeader();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $token_header);
            
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        /*$result = json_decode($response, true);*/
        return $response;
    }

    public function attachmentCurl($url_code,$token_header){
        
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
        return $response;
    }
}