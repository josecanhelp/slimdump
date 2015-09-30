<?php
namespace Webfactory\Slimdump\Config;

class Table
{
    private $name;
    private $dump;

    private $columns = array();

    public function __construct($config)
    {
        $attr = $config->attributes();
        $this->name = (string) $attr->name;

        $const = 'Webfactory\Slimdump\Config\Config::' . strtoupper((string)$attr->dump);

        if (defined($const)) {
            $this->dump = constant($const);
        } else {
            throw new \RuntimeException(sprintf("Invalid dump type %s for table %s.", $this->dump, $this->name));
        }

        foreach ($config->column as $columnConfig) {
            $column = new Column($columnConfig);
            $this->columns[$column->getName()] = $column;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function isSchemaDumpRequired()
    {
        return $this->dump >= Config::SCHEMA;
    }

    public function isDataDumpRequired()
    {
        return $this->dump >= Config::NOBLOB;
    }

    public function getSelectExpression($columnName, $isBlobColumn)
    {
        if ($isBlobColumn) {
            if ($this->dump == Config::NOBLOB) {
                return 'NULL';
            } else {
                return "IF(ISNULL(`$columnName`), NULL, IF(`$columnName`='', '', CONCAT('0x', HEX(`$columnName`))))";
            }
        } else {
            return $columnName;
        }
    }

    public function getStringForInsertStatement($columnName, $value, $isBlobColumn, $db)
    {
        if ($value === null) {
            return 'NULL';
        } else if ($value === '') {
            return '""';
        } else if ($isBlobColumn) {
            return $value;
        } else {
            return $db->quote($value);
        }
    }
}
