<?php

namespace App\Component;

use Model;
use Nette;

class ExpenseList extends \Nette\Application\UI\Control
{
    /**
     * @var Model\ExpenseType
     */
    protected $expenseTypeRepository;

    /**
     * @var Model\Expense
     */
    protected $expenseRepository;

    public function __construct(Model\Expense $expenseRepository, Model\ExpenseType $expenseTypeRepository)
    {
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->expenseRepository = $expenseRepository;
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
        $grid->addColumn('date', 'Datum')->enableSort();
        $grid->addColumn('title', 'Popis');
        $grid->addColumn('expense_type_title', 'Kategorie');
        $grid->addColumn('price', 'Cena');
        $grid->addColumn('priority', 'Priorita');
        $grid->addColumn('periodic_payment_id', 'Pravidelné');

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $params = $this->presenter->request->getParameters();
        if (!isset($params['month'])) {
            $params['month'] = date('n');
        }
        if (!isset($params['year'])) {
            $params['year'] = date('Y');
        }

        $grid->setDatasourceCallback(function ($filter, $order) {
             return $this->expenseRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function () use ($params) {
            $form = new Nette\Forms\Container;
            
            $typePairs = $this->expenseTypeRepository->findAll()->fetchPairs('id', 'title');
            
            $form->addContainer('expense_type_title');
            $form['expense_type_title']->addSelect('expense_type_id', 'Typ výdaje', $typePairs)
                                       ->setPrompt('- Vyberte -')
                                       ->setAttribute('class', 'form-control');
                 
            $form->addCheckbox('group_type', ' Seskupit podle typu')
                 ->setAttribute('onchange', 'this.form.submit()');
            
             $form->addContainer('date');
             $form['date']->addSelect('month', 'Měsíc', array_combine(range(1, 12), range(1, 12)))
                          ->setAttribute('style', 'width: 70px; display: inline;')
                          ->setAttribute('class', 'form-control');

             $form['date']->addSelect('year', 'rok', array_combine(range(2012, date('Y')), range(2012, date('Y'))))
                          ->setAttribute('style', 'width: 80px; display: inline;')
                          ->setAttribute('class', 'form-control');

            $form->addSelect('priority', 'Priorita', array_combine(Model\Expense::getPriorities(), Model\Expense::getPriorities()))
                 ->setPrompt('-')
                 ->setAttribute('style', 'width: 60px; display: inline')
                 ->setAttribute('class', 'form-control');

            $form->addSelect('periodic_payment_id', 'Pravidelné', [1 => 'ne', 2 => 'ano'])
                 ->setPrompt('-')
                 ->setAttribute('style', 'width: 80px; display: inline')
                 ->setAttribute('class', 'form-control');
                            
            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

            $form['date']->setDefaults($params);

            return $form;
        });

        return $grid;
    }
}
