<?php

namespace Model;

use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
abstract class Repository extends Nette\Object
{
    /** @var string Table name */
    protected $tableName;

    /** @var Nette\Database\Connection */
    protected $connection;

    protected $defaultOrder = ['id', 'DESC'];
    
    protected $filterColumns = [
        'like'  => [],
        'equal' => [],
    ];

    public function __construct(\Nette\Database\Context $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Vraci nazev tabulky
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Vrací objekt reprezentující databázovou tabulku.
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->connection->table($this->tableName);
    }

    /**
     * Vrací všechny řádky z tabulky.
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vrací řádky podle filtru, např. array('name' => 'John').
     * @return Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }
    
    /**
     * Vrací záznamu podle INT primary key
     * @param $id
     */
    public function findRow($id)
    {
        return $this->getTable()->get((int) $id);
    }
    
    /**
     * Vkládá data do tabulky
     * @param $data
     */
    public function insert($data)
    {
        return $this->getTable()->insert($data);
    }
    
    /**
     * Vymaže záznam podle primárního klíče
     * @param $id
     */
    public function delete($id)
    {
        return $this->findBy(array($this->getTable()->getPrimary() => (int) $id))->delete();
    }

    /*
     * Ulozi nebo updatne zaznam
     */
    public function save($data, $id = 0)
    {
        $id = (int) $id;

        if (0 === $id) {
            $record = $this->insert($data);
        } else {
            $record = $this->findRow($id);

            $record->update($data);
        }

        return $record;
    }

    /**
     *
     *
     * @return array kolekce sloupcu databaze
     */
    public function getColumns($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->getTableName();
        }

        $columns = $this->connection->getSupplementalDriver()->getColumns($tableName);

        $columnsResult = array();

        foreach ($columns as $column) {
            $columnsResult[] = $column['name'];
        }

        return $columnsResult;
    }

    /**
     * Vrati odfiltrovana data tak, ze obsahuji indexy jen existujicich sloupcu
     * v tabulce
     *
     * @return array data jen s indexy existujich sloupcu v tabulce
     */
    protected function getFilteredData($data)
    {
        $columns = $this->getColumns();

        foreach ($data as $key => $value) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    public function insertFiltered($data)
    {
        $data = $this->getFilteredData($data);

        return $this->insert($data);
    }

    public function saveFiltered($data, $id = 0)
    {
        $data = (array) $data;
        $data = $this->getFilteredData($data);

        return $this->save($data, $id);
    }

    public function findRows($filter, $order)
    {
        $filters = [];

        foreach ($filter as $column => $value) {
            if (!empty($this->filterColumns['like']) && in_array($column, $this->filterColumns['like'])) {
                $filters[$column . ' LIKE ?'] = "%{$value}%";
            } elseif (!empty($this->filterColumns['equal']) && in_array($column, $this->filterColumns['equal'])) {
                $filters[$column] = $value;
            }
        }

        if (empty($order[0])) {
            $order = $this->defaultOrder;
        }

        $rows = $this->findAll()
                     ->where($filters)
                     ->order(implode(' ', $order));

        return $rows;
    }
}
