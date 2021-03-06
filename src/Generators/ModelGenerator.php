<?php

namespace LaraSpells\Generator\Generators;

use LaraSpells\Generator\Schema\Table;
use LaraSpells\Generator\Traits\Concerns\TableUtils;

class ModelGenerator extends ClassGenerator
{

    use Concerns\TableUtils;

    const CLASS_MODEL = 'Illuminate\Database\Eloquent\Model';

    public function __construct(Table $tableSchema)
    {
        parent::__construct($tableSchema->getModelClass());
        $this->tableSchema = $tableSchema;
        $this->initClass();
        $relations = $this->getTableSchema()->getRelations();
        foreach($relations as $relation) {
            $this->addRelationMethod($relation['table'], $relation['type'], $relation['key_from'], $relation['key_to']);
        }
    }

    protected function initClass()
    {
        $data = $this->getTableData();
        $usingSoftDelete = $this->tableSchema->usingSoftDelete();
        $fillables = $this->tableSchema->getFillableColumns();
        $this->setParentClass(static::CLASS_MODEL);
        $this->addProperty('table', 'string', 'protected', $data->table_name, 'Table name');
        $this->addProperty('fillable', 'array', 'protected', $fillables, 'Fillable columns');
        $this->addProperty('primaryKey', 'string', 'protected', $data->primary_key, 'The primary key for the model');

        if ($usingSoftDelete) {
            $this->useTrait('Illuminate\Database\Eloquent\SoftDeletes');
            $this->addProperty('dates', 'array', 'protected', ['deleted_at'], 'The attributes that should be mutated to dates.');
        }

        $this->setDocblock(function($docblock) use ($data) {
            $authorName = $this->tableSchema->getRootSchema()->getAuthorName();
            $authorEmail = $this->tableSchema->getRootSchema()->getAuthorEmail();
            $docblock->addText("Generated by LaraSpell");
            $docblock->addAnnotation("author", "{$authorName} <{$authorEmail}>");
            $docblock->addAnnotation("created", date('r'));
        });
    }

    protected function addRelationMethod($table, $type, $keyFrom, $keyTo)
    {
        $relatedTable = $this->getTableSchema()->getRootSchema()->getTable($table);
        $modelClass = $relatedTable->getModelClass(true);
        $isHasOne = in_array($type, ['has-one']);
        if ($isHasOne) {
            $from = preg_replace("/(^id_|_id$)/", "", $keyFrom);
            $methodName = camel_case($from);
        } else {
            $methodName = camel_case($relatedTable->getName());
        }

        $method = $this->addMethod($methodName);
        $relationMethod = camel_case($type);
        $returnClass = "Illuminate\\Database\\Eloquent\\Relations\\".ucfirst($relationMethod);

        $method->setDocblock(function($docblock) use ($table, $returnClass) {
            $docblock->addText("Relation to table '{$table}'");
            $docblock->setReturn($returnClass);
        });

        $relationParams = [];
        $relationParams[] = "'{$modelClass}'";
        $relationParams[] = "'{$keyTo}'";
        $relationParams[] = "'{$keyFrom}'";
        $relationParams = implode(", ", $relationParams);

        $method->addCode("return \$this->{$relationMethod}({$relationParams});");
    }

}
