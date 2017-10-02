<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Zendesk\Controller\Adminhtml\App\Ajax;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Install extends Action
{
    const ZENDESK_M2_APP_ID = "136052"; // TODO: @pepe asignar el app id
    const ZENDESK_M2_APP_INSTALLATION_DATA = 'zendesk/m2_app/installation_data';

    /**
     * @var \Wagento\Zendesk\Helper\Api\Apps
     */
    private $apps;
    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    private $integrationService;
    /**
     * @var \Magento\Integration\Api\OauthServiceInterface
     */
    private $oauthService;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Install constructor.
     * @param Action\Context $context
     * @param \Wagento\Zendesk\Helper\Api\Apps $apps
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        \Wagento\Zendesk\Helper\Api\Apps $apps,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->apps = $apps;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // ajax validation
        if (!$this->getRequest()->isAjax()) {
            return $this->_forward('noroute');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $config = $this->scopeConfig->getValue(self::ZENDESK_M2_APP_INSTALLATION_DATA);
        $data = json_decode($config, true);;

        if ((isset($data['integration_id']) && is_numeric($data['integration_id']))
            && isset($data['installation_id']) && is_numeric($data['installation_id'])) {
            return $resultJson->setData(['error' => 'Nothing to install.']);
        }

        $integrationId = null;
        $installationId = null;
        $res = [];

        if (isset($data['integration_id'])) {
            $integrationId = $data['integration_id'];
        } else {
            // Generate Token
            $integrationData = [
                'name' => 'm2appzendesk',
                'email' => '',
                'endpoint' => '',
                'identity_link_url' => '',
                'current_password' => '',
                'all_resources' => '',
                'resource' => [
                    'Wagento_Zendesk::zendesk_api'
                ],
            ];

            try {
                /** @var \Magento\Integration\Model\Integration $integration */
                $integration = $this->integrationService->create($integrationData);
                $integrationId = $integration->getConsumerId();
                $clearExistingToken = (int)$this->getRequest()->getParam('reauthorize', 0);
                if ($this->oauthService->createAccessToken($integrationId, $clearExistingToken)) {
                    $integration->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)->save();
                }
            } catch (\Exception $exception) {
                return $resultJson->setData(['error' => 'Something went wrong.']);
            }
        }

        if (!isset($data['installation_id']) && is_numeric($integrationId)) {

            /** @var \Magento\Integration\Model\Oauth\Token $accessToken */
            $accessToken = $this->oauthService->getAccessToken($integrationId);
            if ($accessToken) {

                $appParams = [
                    'name' => 'Magento2 App',
                    'domain' => $this->getUrl('/'),
                    'token' => $accessToken->getToken(),
                ];

                // Install App
                $installationId = $this->apps->installApp(self::ZENDESK_M2_APP_ID, $appParams);
                if (is_numeric($installationId)) {
                    $res = ['success' => 'Successfully installed'];
                } else {
                    $res = ['error' => 'Can\'t install APP.'];
                }
            }
        }

        $data = [
            'integration_id' => $integrationId,
            'installation_id' => $installationId
        ];
        // saved integration id in zendesk/m2_app/installation_id config path
        $this->configWriter->save(self::ZENDESK_M2_APP_INSTALLATION_DATA, json_encode($data));
        return $resultJson->setData($res);
    }
}
