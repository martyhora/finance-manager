<?php

namespace App\Component;

use Model;
use Nette;

class CategoryList extends \Nette\Application\UI\Control
{
    /**
     * @var Model\ExpenseType
     */
    protected $expenseTypeRepository;

    public function __construct(Model\ExpenseType $expenseTypeRepository)
    {
        $this->expenseTypeRepository = $expenseTypeRepository;
    }

    public function render()
    {
        $reflection = new \ReflectionClass(get_class($this));

        $this->template->setFile(__DIR__ . '/' . $reflection->getShortName() . '.latte');

        $this->template->render();
    }

    public function createComponentGrid()
    {
        $grid = new \Nextras\Datagrid\Datagrid;
        $grid->addColumn('title', 'Název')->enableSort();
        // $grid->addColumn('actions', 'Akce');

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function ($filter, $order) {
             return $this->expenseTypeRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function () {
            $form = new Nette\Forms\Container;
            $form->addText('title')->setAttribute('class', 'form-control');

            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

            return $form;
        });

        return $grid;
    }
}
