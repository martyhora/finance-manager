<?php

namespace Model;

class Limit extends Repository
{
    protected $tableName = 'limit';

    protected $defaultOrder = ['CONCAT(year, "-", month)', 'DESC'];
    
    public function getLimit($year, $month)
    {
        $row = $this->findBy(['year' => $year, 'month' => $month])->fetch();

        if (empty($row->limit)) {
            return ['limit' => 0];
        }

        return $row;
    }
}
