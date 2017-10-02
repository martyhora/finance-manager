<?php

namespace Model;

class ExpenseType extends Repository
{
    protected $tableName = 'expense_type';

    protected $filterColumns = [
        'like'  => ['title'],
    ];
}
