<?php

namespace App\Component;

use Nette;
use Nette\Application\UI\Form;
use Model;

class Statistic extends \Nette\Application\UI\Control
{
    /**
     * @var Model\Expense
     */
    protected $expenseRepository;

    /** @var Model\ExpenseType */
    protected $expenseTypeRepository;

    /**
     * Data statistik v JSONu
     * @var string
     */
    protected $statsData;

    public function __construct(Model\Expense $expenseRepository, Model\ExpenseType $expenseTypeRepository)
    {
        parent::__construct();

        $this->expenseRepository     = $expenseRepository;
        $this->expenseTypeRepository = $expenseTypeRepository;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/Statistic.latte');
        
        $year  = date('Y');

        $defaults = ['date_start' => date('Y') . "-" . date('m') . "-01", 'date_end' => date('Y') . "-" . date('m') . "-" . date('t')];

        $this['statsFilterForm']->setDefaults($defaults);

        if (!$this->statsData) {
            $this->statsData = $this->expenseRepository->getStatsData($defaults);
        }

        $this->template->statsData = $this->statsData;

        $this->template->render();
    }

    public function statsFilterFormSubmit()
    {
        $this->statsData = $this->expenseRepository->getStatsData($this->presenter->getParameters());
        $this->redrawControl('statisticsDashboard');
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentStatsFilterForm()
    {
        $form = new BootstrapForm();
        $form->setMethod(Form::GET);
        
        $typePairs = $this->expenseTypeRepository->findAll()->fetchPairs('id', 'title');
        
        $form->addSelect('type', 'Kategorie', $typePairs)
             ->setAttribute('class', 'form-control')
             ->setPrompt('- Vyberte -');

        $form->addDate('date_start', 'Od', \Vodacek\Forms\Controls\DateInput::TYPE_DATE)->setAttribute('class', 'form-control');
        $form->addDate('date_end', 'Do', \Vodacek\Forms\Controls\DateInput::TYPE_DATE)->setAttribute('class', 'form-control');
             
        $form->addSubmit('filter', ' Filtrovat ')->setAttribute('class', 'btn btn-primary btn-flat ajax');

        $form->onSuccess[] = [$this, 'statsFilterFormSubmit'];

        return $form;
    }
}
