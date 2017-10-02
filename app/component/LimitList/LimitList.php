<?php

namespace App\Component;

use Model;

class LimitList extends \Nette\Application\UI\Control
{
    /**
     * @var Model\Limit
     */
    protected $limitRepository;

    public function __construct(Model\Limit $limitRepository)
    {
        $this->limitRepository = $limitRepository;
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
        $grid->addColumn('month', 'Měsíc')->enableSort();
        $grid->addColumn('year', 'Rok')->enableSort();
        $grid->addColumn('limit', 'Limit')->enableSort();
        // $grid->addColumn('actions', 'Akce');

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function ($filter, $order) {
             return $this->limitRepository->findRows($filter, $order);
        });

        return $grid;
    }
}
