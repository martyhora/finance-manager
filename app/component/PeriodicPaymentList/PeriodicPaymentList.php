<?php

namespace App\Component;

use Model;

class PeriodicPaymentList extends \Nette\Application\UI\Control
{
    /**
     * @var Model\ExpenseType
     */
    protected $expenseTypeRepository;

    /**
     * @var Model\PeriodicPayment
     */
    protected $periodicPaymentRepository;

    public function __construct(Model\PeriodicPayment $periodicPaymentRepository, Model\ExpenseType $expenseTypeRepository)
    {
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->periodicPaymentRepository = $periodicPaymentRepository;
    }

    public function render()
    {
        $reflection = new \ReflectionClass(get_class($this));

        $this->template->setFile(__DIR__ . '/' . $reflection->getShortName() . '.latte');

        $this->template->sumPrice = $this->periodicPaymentRepository->getSum();

        $this->template->render();
    }

    public function createComponentGrid()
    {
        $grid = new \Nextras\Datagrid\Datagrid;
        // $grid->addColumn('date', 'Datum')->enableSort();
        $grid->addColumn('date_start', 'Datum začátku platnosti')->enableSort();
        $grid->addColumn('title', 'Popis');
        $grid->addColumn('expense_type_title', 'Kategorie');
        $grid->addColumn('price', 'Cena');
        $grid->addColumn('priority', 'Priorita');

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function ($filter, $order) {
             return $this->periodicPaymentRepository->findRows($filter, $order);
        });

        return $grid;
    }
}
