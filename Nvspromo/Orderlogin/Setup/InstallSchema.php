<?php
 /**
 *
 * @author        Viha Digital Commerce Team <naeem@vdcstore.com>.
 * @copyright     Copyright(c) 2020 Viha Digital Commerce (https://www.vihadigitalcommerce.com/)
 * @link          https://www.vihadigitalcommerce.com/
 * @date          19/07/2021
 */
namespace Nvspromo\Orderlogin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $context->getVersion();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('zoho_token')
        )->addColumn(
            'zoho_token_id',
            Table::TYPE_INTEGER,
            null,
            ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
        )->addColumn(
            'refresh_token_id',
            Table::TYPE_TEXT,
            255,
            [],
            'refresh_token_id'
        )->addColumn(
            'refresh_token',
            Table::TYPE_TEXT,
            255,
            [],
            'refresh_token'
        );
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}