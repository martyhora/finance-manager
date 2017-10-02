<?php

namespace Model;

class PeriodicPayment extends Repository
{
    protected $tableName = 'periodic_payment';

    protected $filterColumns = [
        'like'  => ['title'],
    ];

    public function getSum()
    {
        return $this->findAll()->select('SUM(price) sum_price')->fetch()->sum_price;
    }
}
