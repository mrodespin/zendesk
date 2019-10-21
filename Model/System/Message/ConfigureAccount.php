<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Zendesk\Model\System\Message;

class ConfigureAccount implements \Magento\Framework\Notification\MessageInterface
{
    const PATH_ADMIN_CHECK = 'zendesk/config/connection_healtcheck_admin';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Wagento\Zendesk\Helper\Api\Connector
     */
    private $connector;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigureAccount constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Wagento\Zendesk\Helper\Api\Connector $connector
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Wagento\Zendesk\Helper\Api\Connector $connector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        $this->urlBuilder = $urlBuilder;
        $this->connector = $connector;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'zendesk_rest_connection';
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->scopeConfig->getValue(self::PATH_ADMIN_CHECK) == 0) {
            return false;
        }
        // Remove zd direct request and do a one time check async
        return !$this->connector->validateConfiguredConnection();
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/zendesk');
        return __('<a href="%1">Click here</a> to configure Zendesk connection.', $url);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
