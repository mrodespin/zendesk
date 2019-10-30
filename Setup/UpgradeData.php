<?php

namespace Wagento\Zendesk\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->addEndSupportNotificationFeed($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addEndSupportNotificationFeed(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $setup->getConnection();

        $notificationTbl = $connection->getTableName('adminnotification_inbox');
        $bind = [
            'severity' => 1,
            'title' => 'Wagento Zendesk Opensource End Support',
            'description' => 'This free version is longer being supported if you would like the most recent version, you can find it on the marketplace. https://marketplace.magento.com/wagento-zendesk.html',
            'url' => 'https://marketplace.magento.com/wagento-zendesk.html'
        ];
        $connection->insertForce($notificationTbl, $bind);
    }
}
