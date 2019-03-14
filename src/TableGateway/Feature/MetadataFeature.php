<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\TableGateway\Exception;
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Source\Factory as SourceFactory;

class MetadataFeature extends AbstractFeature
{
    /**
     * @var MetadataInterface
     */
    protected $metadata = null;

    /**
     * Constructor
     *
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata = null)
    {
        if ($metadata) {
            $this->metadata = $metadata;
        }
        $this->sharedData['metadata'] = [
            'primaryKey' => null,
            'columns' => []
        ];
    }

    public function postInitialize() : void
    {
        if ($this->metadata === null) {
            $this->metadata = SourceFactory::createSourceFromAdapter($this->tableGateway->adapter);
        }

        // localize variable for brevity
        $t = $this->tableGateway;
        $m = $this->metadata;

        // get column named
        $columns = $m->getColumnNames($t->table);
        $t->columns = $columns;

        // set locally
        $this->sharedData['metadata']['columns'] = $columns;

        // process primary key only if table is a table; there are no PK constraints on views
        if (! ($m->getTable($t->table) instanceof TableObject)) {
            return;
        }

        $pkc = null;

        foreach ($m->getConstraints($t->table) as $constraint) {
            /** @var $constraint \Zend\Db\Metadata\Object\ConstraintObject */
            if ($constraint->getType() == 'PRIMARY KEY') {
                $pkc = $constraint;
                break;
            }
        }

        if ($pkc === null) {
            throw new Exception\RuntimeException('A primary key for this column could not be found in the metadata.');
        }

        $pkcColumns = $pkc->getColumns();
        if (count($pkcColumns) === 1) {
            $primaryKey = $pkcColumns[0];
        } else {
            $primaryKey = $pkcColumns;
        }

        $this->sharedData['metadata']['primaryKey'] = $primaryKey;
    }
}
