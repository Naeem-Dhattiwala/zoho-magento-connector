<?php

 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce
 * @link          https://www.vihadigitalcommerce.com/
 * @date          28/12/2021
 */

namespace Nvspromo\Payorder\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Request
     */

    protected $request;

    /**
     * @var RedirectFactory
     */

    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Request\Http
     */
    public function __construct(
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->resultPageFactory     = $resultPageFactory;
        $this->request               = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($this->request->getParam("so_id") || $this->request->getParam("inv_id")){
            $this->resultPage = $this->resultPageFactory->create();  
            $this->resultPage->getConfig()->getTitle()->prepend(__('Pay Order'));
            return $this->resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        }
    }
}

