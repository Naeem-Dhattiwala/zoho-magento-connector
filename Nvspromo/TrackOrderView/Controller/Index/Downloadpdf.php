<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          22/12/2021
 */
 
namespace Nvspromo\TrackOrderView\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Downloadpdf extends \Magento\Framework\App\Action\Action  {

  protected $orderLoginFactory;

  protected $resultPageFactory;

  /**
   * Constructor
   *
   * @param \Magento\Framework\App\Action\Context  $context
   * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
   */

  public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Nvspromo\Orderlogin\Model\Orderlogin $orderLoginFactory,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory
  ) {
      $this->orderLoginFactory = $orderLoginFactory;
      $this->resultPageFactory = $resultPageFactory;
      parent::__construct($context);
  }
  public function execute() {
    $zohotoken = $this->orderLoginFactory->getCollection();
    foreach ($zohotoken as $results) {
      $main_token = $results['refresh_token_id'];
      $AccessToken = $results['refresh_token'];
      $token_header =  json_decode($AccessToken, true);
    }
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('NVS Promo');
    $pdf->SetTitle('NVS Promo');
    $pdf->SetSubject('Sales Order Details');
    $pdf->SetKeywords('Sales Order');

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
      require_once(dirname(__FILE__) . '/lang/eng.php');
      $pdf->setLanguageArray($l);
    }
    $pdf->AddPage();

    $logo_image = '<img style="width:130px" src="https://www.nvspromo.com/skin/frontend/nvs/default/images/logo_pdf.gif'.'" class="small" />';

    $pdf->setFontSubsetting(true);

    if(isset($_REQUEST['so_id'])){
      $id = $_REQUEST['so_id'];
      $sid = base64_decode($id);
      $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$sid.")";
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
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
      $po_no = '';
      foreach($result['data'] as $order){
        $id = $order['id'];
        $sv_subject = $order['Order_Number'];
        $so_id = $order['Order_Number'];
        $notes = $order['Notes'];
        $status = $order['Status'];
        $acc_name = $order['Account_Name']['name'];
        $contact_name =$order['Contact_Name']['name'];
        $phno = $order['Phone_Number']; 
        $order_no = $order['Order_Number'];
        $sv_po_no = $order['PO_Number'];
        $in_hands = $order['In_Hands'];
        $email_id = $order['EMail'];
        $sowner = $order['Owner'];
        $ordertovendor = $order['Order_to_Vendor'];
        $vendor = $order['Vendor'];
        $pi_number = $order['PI_Number'];
        $bill_name = $order['Billing_Name'];
        $bill_contact = $order['Bill_to_Contact'];
        $bill_street = $order['Billing_Street'];
        $bill_city = $order['Billing_City'];
        $bill_state = $order['Billing_State'];
        $bill_Code = $order['Billing_Code'];
        $bill_country = $order['Billing_Country'];
        $shipto = $order['Ship_To'];
        $contact_person = $order['Contact_Person'];
        $shipping_street = $order['Shipping_Street'];
        $shipping_city = $order['Shipping_City'];
        $shipping_state = $order['Shipping_State'];
        $shipping_code = $order['Shipping_Code'];
        $shipping_country = $order['Shipping_Country'];
        $adjustment = $order['Adjustment'];
        $sub_total = $order['Sub_Total'];
        $ttax = $order['Tax'];
        $grand_total = $order['Grand_Total'];
        $ddiscount = $order['Discount'];
        $order_notes = $order['Order_Notes'];
        $art_order = $order['Art_and_Order_Sent'];
        $art_approved = $order['Art_and_Order_Approved'];
        $payment_recieved = $order['Payment_Received'];
        $order_recieved = $order['Order_Received'];
        $paid_in_full = $order['Paid_in_Full'];
        $production = $order['In_Production'];
        $order_shipped = $order['Shipped'];
        $terms = $order['Terms_and_Conditions'];
        $track_number = $order['Tracking_Number']; 
        $track_number_orijinal = $order['Tracking_Number']; 
        $estimated_date = $order['Estimated_Ship_Date'];
        foreach ($order['Product_Details'] as $soLineItem) {
          $prod_name[] =$soLineItem['product']['name'];
          $prod_desc[] =$soLineItem['product_description'];
          $list_price[] = $soLineItem['list_price'];
          $quantity[] = $soLineItem['quantity'];
          $amount[] = $soLineItem['net_total'];
          $discount[] = $soLineItem['Discount'];
          $tax[] = $soLineItem['Tax'];
          $total[] = $soLineItem['total'];
        }
      }
      $count_value = count($prod_name);
      $no =1;
      $html = '<table width="730" align="center" cellpadding="0" cellspacing="1" style=" font-size:12px; border-right-style:solid;border-bottom: 2px solid #ccc; border-left-style:solid;border-color:#CCC;">
          <tr>
            <td width="715" height="151" align="left" valign="bottom">
              <table width="715" align="left" >
                <tr>
                  <td width="217" rowspan="3"  height="48"><br/><div style="margin-top:7px;">'.$logo_image.'</div></td>
                  <td width="285" rowspan="3" align="center" valign="middle"  height="48"><h5>NVS Promotional Designs Inc.</h5>
                      <span style="font-size:11px">1041 W. 18th St., Suite A-207</span><br>
                      <span style="font-size:11px">Costa Mesa, CA 92627</span><br>
                      <span style="font-size:11px; font-weight:bold;">O</span> <span style="font-size:11px">- 877-503-3533</span> <span style="font-size:11px; font-weight:bold;"> F</span> - <span style="font-size:11px">949-272-0545</span></td>
                  <td width="193" align="right" style="font-weight:bold;">Sales Order</td>
                </tr>
                <tr align="center">
                  <td width="193" height="26" align="right"><span style="margin-top: 2px;  margin-bottom: 2px; font-size:12px; font-weight:bold;">Due Date:</span><br><span style="font-size:12px">'.$in_hands.'</span></td>
                </tr>
                <tr align="center">
                  <td width="193" height="48" align="right"><span style="margin-top: 2px;  margin-bottom: 2px; font-size:14px; font-weight:bold;">Sales Order Number:</span><br>
                      <span style="font-size:11px">'.$so_id.'</span></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td width="715" height="131" align="right">
              <table width="715" cellpadding="0" cellspacing="1" align="left" style="border-right-style:solid;border-bottom: 1px solid #ccc; border-left-style:solid; border-color:#CCC;"  >
                  <tr>
                      <td width="349" height="110" align="left" valign="top" >
                          <span style="font-size:13px; font-weight:bold;">BILL TO:</span><br>
                          <strong style="font-size:11px">'.$bill_name.'</strong><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$bill_street.'</span><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$bill_city.','.$bill_state.$bill_Code.'</span><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;"> '.$bill_country.'</span>
                      </td>
                      <td  style="background-color:#F0F0F0;"  width="1"></td>
                      <td width="344" align="left"  valign="top" style="padding-left:4px;" ><span style="font-size:13px; font-weight:bold;">SHIP TO</span><br>
                          <strong style="font-size:11px; ">'.$shipto.'</strong><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$contact_person.'</span><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_street.'</span><br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_city.$shipping_state.$shipping_code.'</span> <br>
                          <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_country.'</span></td></tr>
              </table>
            </td>
          </tr>
          <tr>
              <td width="719" height="40" align="right" style="padding-left:30px;">
                  <table width="719" align="left" cellpadding="4"  style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; border-width:1px;border-color:#CCC;">
                      <tr>
                          <td width="175" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Account Name:</td>
                          <td width="175" ><span style="font-size:11px; font-weight:bold;">'.$acc_name.'</span></td>
                          <td width="148" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Order Tracker:</td>
                          <td width="214" ><span style="font-size:11px; font-weight:bold;"></span></td>

                      </tr>

                      <tr>
                          <td width="175" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;"></td>
                          <td width="175" ><span style="font-size:11px; font-weight:bold;"></span></td>
                          <td width="148" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Account Name:</td>
                          <td width="214" ><span style="font-size:11px; font-weight:bold; ">'.$acc_name.'</span></td>

                      </tr>
                      <tr>
                          <td width="175" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;"></td>
                          <td width="175" ><span style="font-size:11px; font-weight:bold;"></span></td>
                          <td width="148" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Sales Order ID#:</td>
                          <td width="214" ><span style="font-size:11px; font-weight:bold; ">'.$so_id.'</span></td>

                      </tr>

                  </table></td>
          </tr>
          <tr>
              <td width="719" height="114" align="left"  >
                  <table width="719" align="left" cellpadding="2" cellspacing="1" style="border-top:1px solid #ccc; border-bottom: 1px solid #ccc">

                      <tr>
                          <td width="175" rowspan="4" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Contact Person:</td>
                          <td width="175" height="21"><span style="font-size:11px; font-weight:bold;">'.$contact_name.'</span></td>
                          <td width="148" rowspan="4" align="right" valign="top" style="background-color:#F0F0F0;">
                              <span style=" font-size:13px;">Email:</span><br>
                              <span style="font-size:13px;">SO Number:</span><br>
                              <span style=" font-size:13px;">PO Number:</span><br/>
                              <span style=" font-size:13px;">In-Hands:</span></td>
                          <td width="214"><span style="font-size:11px; font-weight:bold;">'.$email_id.'</span></td>
                      </tr>
                      <tr>
                          <td width="175" height="16"><span style="font-size:11px; font-weight:bold;">'.$phno.'</span></td>
                          <td width="214"><span style="font-size:11px; font-weight:bold;">'.$so_id.'</span></td>
                      </tr>
                      <tr>
                          <td width="175" height="16"><span style="font-size:11px;font-weight:bold;">'.$email_id.'</span></td>
                          <td width="214"><span style="font-size:11px; font-weight:bold;">'.$po_no.'</span></td>
                      </tr>
                      <tr>
                          <td width="175" height="16"></td>
                          <td width="214" valign="top"><span style="font-size:11px; font-weight:bold;">'.$in_hands.'</span></td>
                      </tr>
                  </table></td>
          </tr>
          <tr>
              <td width="716" align="left"  style="border-top:1px solid #666; background-color:#F0F0F0;" cellpadding="1" cellspacing="1" >
                  <table width="716" align="left">
                      <tr>
                          <td width="76"  valign="top"  align="left"><span style=" font-size:14px; font-weight:bold;">S.No</span></td>
                          <td width="350"  align="center" ><span style=" font-size:14px; font-weight:bold;">Product Details</span></td>
                          <td width="85"   align="center"><span style="font-size:14px; font-weight:bold;">Qty</span></td>
                          <td width="90"  align="center" ><span style="font-size:14px; font-weight:bold;">Net Price</span></td>
                          <td width="92" align="right" ><span style=" font-size:14px; font-weight:bold;">Total</span></td>

                      </tr></table></td></tr><tr>
              <td width="716" align="left" style="border-top:1px solid #666; " >
                  <table width="716" align="left" cellpadding="1" cellspacing="1">';
                      for($i=0; $i<$count_value;$i++){

                      $html .='<tr>
                          <td width="76"  align="left" valign="top" ><span style="font-size:12px; font-weight:bold;">'.$no.'</span></td>
                          <td width="350" align="left" ><span style="font-size:10px; font-weight:bold;">'.$prod_name[$i].'</span><br />'.nl2br($prod_desc[$i]).'
                          </td>
                          <td width="85"  align="center" ><span style="font-size:10px; font-weight:bold;">'.round($quantity[$i]).'</span></td>
                          <td width="90" align="center"><span style="font-size:10px; font-weight:bold;">$'.number_format($list_price[$i],2).'</span></td>
                          <td width="92" align="right" ><span style="font-size:10px; font-weight:bold;">$'.number_format($total[$i],2).'</span></td>
                      </tr>
                      <tr><td style="border-bottom:1px solid #999" colspan="5"></td></tr>';

                      $no++;}
                      $html .='</table></td></tr>';
        $html .= '<tr><td width="717" align="right"  height="90" style="border-bottom: 1px solid #ccc;">
                <table width="717">
                    <tr>
                        <td width="614" height="21" align="right" >Sub Total :</td>
                        <td width="70"><span style="font-size:13px; font-weight:bold;">$'.number_format($grand_total,2).'</span></td>
                    </tr>
                    <tr>
                        <td width="614" height="21" align="right" >Tax :</td>
                        <td  width="70"><span style="font-size:13px; ">$'.number_format($tax[0],2).'</span></td>
                    </tr>
                    <tr >
                        <td width="614" height="21" align="right">Adjustment :</td>
                        <td  width="70"><span style="font-size:13px;">$'.number_format($adjustment,2).'</span></td>
                    </tr>
                    <tr valign="top"><td width="615" height="2"></td><td width="70" style="border-bottom:1px solid #ccc" ></td></tr>
                    <tr >
                        <td width="614" height="21"  align="right">Total :</td>
                        <td width="70"><span style="font-size:13px; font-weight:bold;">$'.number_format($grand_total,2).'</span></td>
                    </tr>
                </table>
            </td></tr>';
      $html .='<tr><td height="48"></td></tr>
          <tr valign="top" >
              <td width="716" height="58" align="left" style="border-top:2px solid #666; border-bottom: 2px solid #666">
                  Terms and Conditions<br />
                  '.$terms.'
              </td>
          </tr>
          <tr><td height="8"></td></tr>
      </table>';
      $lg = Array();

      $lg['a_meta_charset'] = 'UTF-8';

      $pdf->setLanguageArray($lg);

      $pdf->SetFont('freesans', '', 12);

      $pdf->setPrintFooter(false);

      $pdf->writeHTML($html, true, false, true, false, '');

      $pdf->lastPage();

      $soid = str_replace('SO','',$so_id);
      if(empty( $order_no)){
          $filename = 'Sales_Orders'.$soid.'.pdf';
      } else {
          $filename = 'Sales_Orders_'.$order_no.'.pdf';
      }
      $pdf->Output($filename, 'D');
    }
    if(isset($_REQUEST['inv_id'])){
      $id = $_REQUEST['inv_id'];
      $inv_id = base64_decode($id);
      $zohotoken = $this->orderLoginFactory->getCollection();
      foreach ($zohotoken as $results) {
          $main_token = $results['refresh_token_id'];
          $AccessToken = $results['refresh_token'];
          $token_header =  json_decode($AccessToken, true);
      }
      $url = "https://www.zohoapis.com/crm/v2/Invoices/search?criteria=(id:equals:".$inv_id.")";
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
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

      if($result == ''){
          $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(id:equals:".$inv_id.")";
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
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
      }

        $in_hands = '';
        $po_no = '';
        $order_no = '';
        $in_hands = '';
      foreach($result['data'] as $order){
        $id = $order['id'];
        $inv_id = $order['id'];
        $notes = $order['Notes'];
        $status = $order['Status'];
        $acc_name = $order['Account_Name']['name'];
        $contact_name =$order['Contact_Name']['name'];
        $phno = $order['Phone_Number']; 
        $owner_phone = $order['Phone_Number'];
        if (isset($order['Order_Number'])) {
          $order_no = $order['Order_Number'];
        }
        $sv_po_no = $order['PO_Number'];
        if (isset($order['In_Hands'])) {
          $in_hands = $order['In_Hands'];
        }
        $email_id = $order['EMail'];
        $sowner = $order['Owner'];
        $ordertovendor = $order['Order_to_Vendor'];
        $vendor = $order['Vendor'];
        $pi_number = $order['PI_Number'];
        $bill_name = $order['Billing_Name'];
        $bill_contact = $order['Bill_to_Contact'];
        $bill_street = $order['Billing_Street'];
        $bill_city = $order['Billing_City'];
        $bill_state = $order['Billing_State'];
        $bill_Code = $order['Billing_Code'];
        $bill_country = $order['Billing_Country'];
        $shipto = $order['Ship_To'];
        $contact_person = $order['Contact_Person'];
        $shipping_street = $order['Shipping_Street'];
        $shipping_city = $order['Shipping_City'];
        $shipping_state = $order['Shipping_State'];
        $shipping_code = $order['Shipping_Code'];
        $shipping_country = $order['Shipping_Country'];
        $adjustment = $order['Adjustment'];
        $sub_total = $order['Sub_Total'];
        $ttax = $order['Tax'];
        $grand_total = $order['Grand_Total'];
        $ddiscount = $order['Discount'];
        $order_notes = $order['Order_Notes'];
        $art_order = $order['Art_and_Order_Sent'];
        $art_approved = $order['Art_and_Order_Approved'];
        $payment_recieved = $order['Payment_Received'];
        $order_recieved = $order['Order_Received'];
        $paid_in_full = $order['Paid_in_Full'];
        $production = $order['In_Production'];
        $order_shipped = $order['Shipped'];
        $terms = $order['Terms_and_Conditions'];
        $track_number = $order['Tracking_Number']; 
        $track_number_orijinal = $order['Tracking_Number']; 
        $estimated_date = $order['Estimated_Ship_Date']; 
        foreach ($order['Product_Details'] as $soLineItem) {
          $prod_name[] =$soLineItem['product']['name'];
          $prod_desc[] =$soLineItem['product_description'];
          $list_price[] = $soLineItem['list_price'];
          $quantity[] = $soLineItem['quantity'];
          $amount[] = $soLineItem['net_total'];
          $discount[] = $soLineItem['Discount'];
          $tax[] = $soLineItem['Tax'];
          $total[] = $soLineItem['total'];
        }
      }
      $count_value = count($prod_name);
      $no =1;
               
      $html = '<table width="730" align="center" cellpadding="0" cellspacing="1" style=" font-size:12px; border-right-style:solid;border-bottom: 2px solid #ccc; border-left-style:solid;border-color:#CCC;"   >
              <tr>
                  <td width="715" height="151" align="left" valign="bottom">
                      <table width="715" align="left" >
                          <tr>
                              <td width="217" rowspan="3"  height="48"><br/><div style="margin-top:7px;">'.$logo_image.'</div></td>
                              <td width="285" rowspan="3" align="center" valign="middle"  height="48"><h5>NVS Promotional Designs Inc.</h5>
                                  <span style="font-size:11px">1041 W. 18th St., Suite A-207</span><br>
                                  <span style="font-size:11px">Costa Mesa, CA 92627</span><br>
                                  <span style="font-size:11px; font-weight:bold;">O</span> <span style="font-size:11px">- 877-503-3533</span> <span style="font-size:11px; font-weight:bold;"> F</span> - <span style="font-size:11px">949-272-0545</span></td>
                              <td width="193" align="right" style="font-weight:bold;">
                                  Invoice
                              </td>
                          </tr>
                          <tr align="center">
                              <td width="193" height="26" align="right"><span style="margin-top: 2px;  margin-bottom: 2px; font-size:12px; font-weight:bold;">Invoice Date:</span>
                                  <span style="font-size:12px">'.$in_hands.'</span></td></tr>
                          <tr align="center">
                              <td width="193" height="48" align="right"><span style="margin-top: 2px;  margin-bottom: 2px; font-size:14px; font-weight:bold;">Tracking Number:</span><br>
                                  <span style="font-size:11px">'.$track_number.'</span></td>
                          </tr>
                      </table>
                  </td>
              </tr>
              <tr>
                  <td width="715" height="131" align="right">
                      <table width="715" cellpadding="0" cellspacing="1" align="left" style="border-right-style:solid;border-bottom: 1px solid #ccc; border-left-style:solid; border-color:#CCC;"  >
                          <tr>
                              <td width="349" height="110" align="left" valign="top" >
                                  <span style="font-size:13px; font-weight:bold;">BILL TO:</span><br>
                                  <strong style="font-size:11px">'.$bill_name.'</strong><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$bill_street.'</span><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$bill_city.','.$bill_state.$bill_Code.'</span><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;"> '.$bill_country.'</span>
                              </td>
                              <td  style="background-color:#F0F0F0;"  width="1"></td>
                              <td width="344" align="left"  valign="top" style="padding-left:4px;" ><span style="font-size:13px; font-weight:bold;">SHIP TO</span><br>
                                  <strong style="font-size:11px; ">'.$shipto.'</strong><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$contact_person.'</span><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_street.'</span><br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_city.$shipping_state.$shipping_code.'</span> <br>
                                  <span style="font-size:11px; margin-top: 2px;  margin-bottom: 2px;">'.$shipping_country.'</span></td></tr>
                      </table>
                  </td>
              </tr>
              <tr>
                  <td width="719" height="40" align="right" style="padding-left:30px;">
                      <table width="719" align="left" cellpadding="4" cellspacing="1"  style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; border-width:1px;border-color:#CCC;">
                          <tr>
                              <td width="175" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;">Account Name:</td>
                              <td width="175" ><span style="font-size:11px; font-weight:bold;">'.$acc_name.'</span></td>
                              <td width="148"  align="right" valign="top" style="background-color:#F0F0F0;"><span style="background-color:#F0F0F0; font-size:13px;">Contact Person:</span></td>
                              <td width="214"><span style="font-size:11px; font-weight:bold;"></span>'.$sowner['name'].'</td>
                          </tr>
                          <tr>
                              <td width="175" align="right" valign="middle" style="background-color:#F0F0F0; font-size:13px;"></td>
                              <td width="175" ></td>
                              <td width="148"  align="right" valign="top" style="background-color:#F0F0F0;"> <span style="background-color:#F0F0F0; font-size:13px;">Phone Number</span></td>
                              <td width="214">'.$owner_phone.'</td>
                          </tr>
                      </table></td>
              </tr>
              <tr>
                  <td width="719" height="114" align="left"  >
                      <table width="719" align="left" cellpadding="2" cellspacing="1" style="border-top:1px solid #ccc; border-bottom: 1px solid #ccc">

                          <tr>
                              <td width="175" rowspan="4" align="right" valign="top" style="background-color:#F0F0F0; font-size:13px;">Contact Person:</td>
                              <td width="175" height="21"><span style="font-size:11px; font-weight:bold;">'.$contact_name.'</span></td>
                              <td width="148" rowspan="4" align="right" valign="top" style="background-color:#F0F0F0;">
                                  <span style=" font-size:13px;"></span><br>
                                  <span style="font-size:13px;">Sales Order:</span><br>
                                  <span style=" font-size:13px;">Purchase order:</span><br/>
                                  <span style=" font-size:13px;"></span></td>
                              <td width="214"><span style="font-size:11px; font-weight:bold;"></span></td>
                          </tr>
                          <tr>
                              <td width="175" height="16"><span style="font-size:11px; font-weight:bold;">'.$phno.'</span></td>
                              <td width="214"><span style="font-size:11px; font-weight:bold;">'.$inv_id.'</span></td>
                          </tr>
                          <tr>
                              <td width="175" height="16"><span style="font-size:11px;font-weight:bold;">'.$email_id.'</span></td>
                              <td width="214"><span style="font-size:11px; font-weight:bold;">'.$po_no.'</span></td>
                          </tr>
                          <tr>
                              <td width="175" height="16"></td>
                              <td width="214" valign="top"><span style="font-size:11px; font-weight:bold;"></span></td>
                          </tr>
                      </table></td>
              </tr>
              <tr>
                  <td width="716" align="left"  style="border-top:1px solid #666; background-color:#F0F0F0;" cellpadding="1" cellspacing="1" >
                      <table width="716" align="left">
                          <tr>
                              <td width="76"  valign="top"  align="left"><span style=" font-size:14px; font-weight:bold;">S.No</span></td>
                              <td width="350"  align="center" ><span style=" font-size:14px; font-weight:bold;">Product Details</span></td>
                              <td width="85"   align="center"><span style="font-size:14px; font-weight:bold;">Qty</span></td>
                              <td width="90"  align="center" ><span style="font-size:14px; font-weight:bold;">Net Price</span></td>
                              <td width="92" align="right" ><span style=" font-size:14px; font-weight:bold;">Total</span></td>

                          </tr></table></td></tr><tr>
                  <td width="716" align="left" style="border-top:1px solid #666; " >
                      <table width="716" align="left" cellpadding="1" cellspacing="1">';
                          for($i=0; $i<$count_value;$i++){

                          $html .='<tr>
                              <td width="76"  align="left" valign="top" ><span style="font-size:12px; font-weight:bold;">'.$no.'</span></td>
                              <td width="350" align="left" ><span style="font-size:10px; font-weight:bold;">'.$prod_name[$i].'</span><br />'.nl2br($prod_desc[$i]).'
                              </td>
                              <td width="85"  align="center" ><span style="font-size:10px; font-weight:bold;">'.round($quantity[$i]).'</span></td>
                              <td width="90" align="center"><span style="font-size:10px; font-weight:bold;">$'.number_format($list_price[$i],2).'</span></td>
                              <td width="92" align="right" ><span style="font-size:10px; font-weight:bold;">$'.number_format($total[$i],2).'</span></td>
                          </tr>
                          <tr><td style="border-bottom:1px solid #999" colspan="5"></td></tr>';

                          $no++;}
                          $html .='</table></td></tr>';
              $html .= '<tr><td width="717" align="right"  height="90" style="border-bottom: 1px solid #ccc;">
                      <table width="717">
                          <tr>
                              <td width="614" height="21" align="right" >Sub Total :</td>
                              <td width="70"><span style="font-size:13px; font-weight:bold;">$'.number_format($grand_total,2).'</span></td>
                          </tr>
                          <tr>
                              <td width="614" height="21" align="right" >Tax :</td>
                              <td  width="70"><span style="font-size:13px; ">$'.number_format($tax[0],2).'</span></td>
                          </tr>
                          <tr >
                              <td width="614" height="21" align="right">Adjustment :</td>
                              <td  width="70"><span style="font-size:13px;">$'.number_format($adjustment,2).'</span></td>
                          </tr>
                          <tr valign="top"><td width="615" height="2"></td><td width="70" style="border-bottom:1px solid #ccc" ></td></tr>
                          <tr >
                              <td width="614" height="21"  align="right">Total :</td>
                              <td width="70"><span style="font-size:13px; font-weight:bold;">$'.number_format($grand_total,2).'</span></td>
                          </tr>
                      </table>
                  </td></tr>';
              $html .='<tr><td height="48"></td></tr>
              <tr valign="top" >
                  <td width="716" height="58" align="left" style="border-top:2px solid #666; border-bottom: 2px solid #666">
                      Terms and Conditions<br />
                      '.$terms.'
                  </td>
              </tr>
              <tr><td height="8"></td></tr>
          </table>';
      $lg = Array();
      $lg['a_meta_charset'] = 'UTF-8';
      $pdf->setLanguageArray($lg);
      $pdf->SetFont('freesans', '', 12);
      $pdf->setPrintFooter(false);
      $pdf->writeHTML($html, true, false, true, false, '');
      $pdf->lastPage();
      //$invid = str_replace('INV','',$inv_id);
      $filename = 'Invoice'.'.pdf';
      $pdf->Output($filename, 'D');
    }
  }              
}
?>