<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddEmailField implements Migration
{
    /**
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$schema->hasTable('synolia_stock_alert')) {
            return;
        }

        $table = $schema->getTable('synolia_stock_alert');
        if ($table->hasColumn('recipient_email')) {
            return;
        }

        $table->addColumn(
            'recipient_email',
            Types::STRING,
            [
                'notnull' => false,
                'oro_options' => [
                    'extend' => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM, 'nullable' => true],
                ],
            ]
        );
    }
}
