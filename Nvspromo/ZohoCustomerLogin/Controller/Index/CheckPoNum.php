<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */

namespace Nvspromo\ZohoCustomerLogin\Controller\Index;

use Magento\Framework\Controller\ResultFactory;    

class CheckPoNum extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Nvspromo\Orderlogin\Model\OrderloginFactory $orderLoginFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->orderLoginFactory = $orderLoginFactory;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
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
    public function execute()
    {
        $collection = $this->orderLoginFactory->create()->getCollection();
        foreach ($collection as $results) {
            $AccessTokenId = $results['refresh_token_id'];
            $AccessToken = $results['refresh_token'];
            $token_header =  json_decode($AccessToken, true);
        }
        $pwd = $_REQUEST['sales_id'];
        $pwd = str_replace(' ', '+', $pwd);
        $pwd = preg_replace('/[^A-Za-z0-9+\-]/', '', $pwd);
        $r=array();
        $url_code_Invoices = "https://www.zohoapis.com/crm/v2/Invoices/search?page=1&per_page=20&criteria=((PO_Number:equals:".$pwd.")and(Order_Received:equals:true))";
        $url_code_sales = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?page=1&per_page=20&criteria=((PO_Number:equals:".$pwd.")and(Order_Received:equals:true))";

        $result = $this->sendCurl($url_code_sales,$token_header);
        if(empty($result['data'])){
            $result = $this->sendCurl($url_code_Invoices,$token_header);   
        }
        if (isset($result['data'])) {
            foreach ($result['data'] as $key => $orderdata) {
                $SalesOrder_id = $orderdata['id'];
                $PO_Number     = $orderdata['PO_Number'];
                $Pay           = $orderdata['Paid_in_Full'];
                $Ship          = $orderdata['Shipped'];
                $Created_date  = $orderdata['Created_Time'];
                $Status        = $orderdata['Status'];
                if(empty($Pay)) {
                    $Pay = 'false';
                } else {
                    $Pay = 'true';
                }
                $Paid = $Pay;
                if(empty($Ship)){
                    $Ship = 'false';
                } else {
                    $Ship = 'true';
                }
                $Shipped =  $Ship;
                /*$date1 = date('Y-m-d', strtotime($Created_date));
                $date2 = date("Y-m-d");

                $ts1 = strtotime($date1);
                $ts2 = strtotime($date2);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);

                echo $diff = (($year2 - $year1) * 12) + ($month2 - $month1);*/
                $Order_date = strtotime("2016-08-21");
            }
            if(!(($Paid == 'true') && (($Shipped == 'true')))) {
                $Order_id = base64_encode($SalesOrder_id);
                echo '?so_id='.$Order_id;
            }
            else{
                $Order_id = base64_encode($SalesOrder_id);
                echo '?inv_id='.$Order_id;
            }
        }
    }
}
