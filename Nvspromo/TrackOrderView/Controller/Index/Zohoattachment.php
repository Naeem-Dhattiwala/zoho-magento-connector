<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          22/12/2021
 */

namespace Nvspromo\TrackOrderView\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Zohoattachment implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_helper;

    protected $attachmentFactory;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Nvspromo\Orderlogin\Helper\Data $helper
     * @param \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        \Nvspromo\Orderlogin\Helper\Data $helper,
        \Nvspromo\TrackOrderView\Model\AttachmentFactory $attachmentFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_attachmentFactory = $attachmentFactory;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $token_header = $this->_helper->TokenHeader();
        $attachment_url = "https://www.zohoapis.com/crm/v2/Attachments";
        $attachment_result = $this->_helper->sendCurl($attachment_url, $token_header);
        $current_date = date("Y-m-d");
        $pdf_url = $_SERVER['DOCUMENT_ROOT'].'/pub/media/nvspromo/artwork/pdf/';
        $image_url = $_SERVER['DOCUMENT_ROOT'].'/pub/media/nvspromo/artwork/image/';
        $thumb_image_url = $_SERVER['DOCUMENT_ROOT'].'/pub/media/nvspromo/artwork/image/thumb/';
        foreach ($attachment_result['data'] as $attachment_key => $attachment_results) {
            $attachment_id[] = $attachment_results['id'];
            $order_id[] = $attachment_results['Parent_Id']['id'];
            $module[] = $attachment_results['$se_module'];
            $created_time[] = $attachment_results['Created_Time'];
            $zoho_date = date("Y-m-d", strtotime($created_time[$attachment_key]));
            if($module[$attachment_key] == 'Sales_Orders' || $module[$attachment_key] == 'Invoices' ){

                if($module[$attachment_key] == 'Sales_Orders'){
                    $url = "https://www.zohoapis.com/crm/v2/Sales_Orders/".$order_id[$attachment_key]."/Attachments/".$attachment_id[$attachment_key];
                } else {
                    $url = "https://www.zohoapis.com/crm/v2/Invoices/".$order_id[$attachment_key]."/Attachments/".$attachment_id[$attachment_key];
                }
                $response = $this->_helper->attachmentCurl($url, $token_header);
                $attachment_id_encoded = base64_encode($order_id[$attachment_key]);
                $file_pdf = $pdf_url.$attachment_id_encoded.'.pdf';
                $file_img = $image_url.$attachment_id_encoded.'.jpg';

                $file_pdf_name = $attachment_id_encoded.'.pdf';
                $file_img_name = $attachment_id_encoded.'.jpg';
                $file_thumb_img_name = $attachment_id_encoded.'.jpeg';

                if (!file_exists($file_pdf)) {
                    $fp = fopen ($pdf_url.$attachment_id_encoded.'.pdf', 'w+');
                    fwrite($fp, $response);
                    fclose($fp);

                }
                if (!file_exists($file_img))  { 
                    
                    $im = new \Imagick();
                    $im->setResolution(300,300);
                    $im->setSize(100,100);
                    $im->readimage($file_pdf); 
                    $im->setImageFormat('jpeg');   
                    $im->setImageCompressionQuality(60);  
                    $im->writeImage($file_img); 
                    $im->clear(); 
                    $im->destroy();

                    //* create image thumbnail
                    $desired_width = 225;
                    $desired_height = 169;
                    $dest  = $thumb_image_url.$attachment_id_encoded.'.jpeg';
                    $source_image = imagecreatefromjpeg($file_img);
                    $width = imagesx($source_image);
                    $height = imagesy($source_image);
                    // $desired_height = floor($height * ($desired_width / $width));
                    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
                    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                    imagejpeg($virtual_image, $dest);

                }
                $attachment = $this->_attachmentFactory->create()->getCollection()
                              ->addFieldToFilter('order_id', $order_id[$attachment_key]);
                if ($attachment->getData()){
                    foreach ($attachment as $attachmentkey => $attachmentvalue) {
                        $entity_id = $attachmentvalue->getId();
                        $attachmentUpdate = $this->_attachmentFactory->create();
                        $attachmentUpdate->load($entity_id, 'attachment_id');
                        $attachmentUpdate->setOrder_id($order_id[$attachment_key]);
                        $attachmentUpdate->setPdf($file_pdf_name);
                        $attachmentUpdate->setPdf_image($file_img_name);
                        $attachmentUpdate->setThumb_image($file_thumb_img_name);
                        $attachmentUpdate->save();
                    }            
                } else {
                    $add_attachment = $this->_attachmentFactory->create();
                    $add_attachment->setOrder_id($order_id[$attachment_key]);
                    $add_attachment->setPdf($file_pdf_name);
                    $add_attachment->setPdf_image($file_img_name);
                    $add_attachment->setThumb_image($file_thumb_img_name);
                    $add_attachment->save();
                }
            }
        }
    }
}
