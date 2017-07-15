<?php

namespace LaraSpell\Generators;

use LaraSpell\Schema\Table;
use LaraSpell\Traits\Concerns\TableUtils;

class RepositoryInterfaceGenerator extends InterfaceGenerator
{
    use Concerns\TableUtils;

    protected $tableSchema;

    public function __construct(Table $tableSchema)
    {
        parent::__construct($tableSchema->getRepositoryInterface());
        $this->tableSchema = $tableSchema;
        $this->initClass();
        $this->addMethodsFromReflection();
    }

    protected function getTableSchema()
    {
        return $this->tableSchema;
    }

    protected function initClass()
    {
        $data = $this->getTableData();
        $fillables = $this->tableSchema->getFillableColumns();
        $this->addImplement($this->tableSchema->getRepositoryClass());
        $this->setDocblock(function($docblock) use ($data) {
            $authorName = $this->tableSchema->getRootSchema()->getAuthorName();
            $authorEmail = $this->tableSchema->getRootSchema()->getAuthorEmail();
            $docblock->addText("Generated by LaraSpell");
            $docblock->addAnnotation("author", "{$authorName} <{$authorEmail}>");
            $docblock->addAnnotation("created", date('r'));
        });
    }

    protected function setMethodAll(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Get all '.$data->label);
            $docblock->setReturn('array');
        });
    }

    protected function setMethodFindById(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->addArgument($data->primary_varname);
        $method->addArgument('options', 'array', []);
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Get '.$data->label.' by '.$data->primary_key);
            $docblock->addParam($data->primary_varname, 'string');
            $docblock->addParam('options', 'array');
            $docblock->setReturn('stdClass|null');
        });
    }

    protected function setMethodGetPagination(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->addArgument('page', null, 1);
        $method->addArgument('limit', null, 10);
        $method->addArgument('options', 'array', []);
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Get pagination data');
            $docblock->addParam('page', 'int');
            $docblock->addParam('limit', 'int');
            $docblock->addParam('options', 'array');
            $docblock->setReturn('array');
        });
    }

    protected function setMethodCreate(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->addArgument('data', 'array');
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Insert new '.$data->label);
            $docblock->addParam('data', 'array');
            $docblock->setReturn('stdClass|null');
        });
    }

    protected function setMethodUpdateById(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->addArgument($data->primary_varname);
        $method->addArgument('data', 'array');
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Update '.$data->label.' by '.$data->primary_key);
            $docblock->addParam($data->primary_varname, 'string');
            $docblock->addParam('data', 'array');
            $docblock->setReturn('bool');
        });
    }

    protected function setMethodDeleteById(MethodGenerator $method)
    {
        $data = $this->getTableData();
        $method->addArgument($data->primary_varname);
        $method->setDocblock(function($docblock) use ($data) {
            $docblock->addText('Delete '.$data->label.' by '.$data->primary_key);
            $docblock->addParam($data->primary_varname, 'string');
            $docblock->setReturn('bool');
        });
    }

}
