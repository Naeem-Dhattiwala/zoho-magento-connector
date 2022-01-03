<?php
declare(strict_types=1);

namespace Nvspromo\ZohoOrderHistory\Controller\Invoiced;

class History extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();  
        $this->resultPage->getConfig()->getTitle()->prepend(__('My Invoices'));
        return $this->resultPage;
    }
}

