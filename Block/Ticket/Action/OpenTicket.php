<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Zendesk\Block\Ticket\Action;


use Magento\Framework\View\Element\Template;

class OpenTicket extends Template
{

    public function __construct(
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function canDisplay()
    {
        // Validate param orderid
        return $this->getData('orderid') && $this->_scopeConfig->getValue('zendesk/ticket/add_order_number');
    }

    /**
     * @return string
     */
    public function getOpenTicketUrl()
    {
        return $this->getUrl('zendesk/ticket/create', ['orderid' => $this->getData('orderid')]);
    }
}
