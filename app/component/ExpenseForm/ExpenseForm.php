<?php

namespace App\Component;

use Model;

class ExpenseForm extends \Nette\Application\UI\Control
{
    /**
     * @var Model\ExpenseType
     */
    protected $expenseTypeRepository;

    /**
     * @var Model\Expense
     */
    protected $expenseRepository;

    protected $defaultPriority = 3;

    public function __construct(Model\ExpenseType $expenseTypeRepository, Model\Expense $expenseRepository)
    {
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->expenseRepository     = $expenseRepository;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ExpenseForm.latte');

        $this->template->render();
    }

    protected function getPriorities()
    {
        return \array_combine(Model\Expense::getPriorities(), Model\Expense::getPriorities());
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new BootstrapForm();
        
        $form->addDate('date', 'Datum', \Vodacek\Forms\Controls\DateInput::TYPE_DATE)
             ->setDefaultValue(\date('d.m.Y'))
             ->setAttribute('class', 'form-control')
             ->addRule(BootstrapForm::FILLED, 'Je nutné zadat datum.');
        
        $form->addTextArea('title', 'Popis')
             ->setAttribute('class', 'form-control')
             ->addRule(BootstrapForm::FILLED, 'Je nutné zadat popis.');
        
        $form->addText('price', 'Cena')
             ->setAttribute('class', 'form-control')
             ->addRule(BootstrapForm::FILLED, 'Je nutné zadat cenu.')
             ->addRule(BootstrapForm::NUMERIC, 'Cena musí být číslo.');

        $typePairs = $this->expenseTypeRepository->findAll()->fetchPairs('id', 'title');
        
        $form->addSelect('expense_type_id', 'Typ výdaje', $typePairs)
             ->setAttribute('class', 'form-control')
             ->setPrompt('- Vyberte -');

        $form->addSelect('priority', 'Priorita', $this->getPriorities())
             ->setAttribute('class', 'form-control')
             ->setDefaultValue($this->defaultPriority);

        $form->addSubmit('set', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = [$this, 'expenseFormSubmitted'];

        return $form;
    }

    public function expenseFormSubmitted(BootstrapForm $form, $values)
    {
        $this->expenseRepository->save($values, $this->presenter->getParam('id'));
        /*if (!$this->isAjax()) {
            $this->redirect('this');
        }*/
        
        $this->presenter->flashMessage('Výdaj byl uložen.', 'success');
        $this->presenter->redirect('Expense:');
    }
}
