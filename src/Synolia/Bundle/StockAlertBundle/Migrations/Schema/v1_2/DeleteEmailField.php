<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DeleteEmailField implements Migration
{
    /**
     * @throws SchemaException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$schema->hasTable('synolia_stock_alert')) {
            return;
        }

        $table = $schema->getTable('synolia_stock_alert');
        if ($table->hasColumn('recipient_email')) {
            $table->dropColumn(
                'recipient_email'
            );
            return;
        }

    }
}
