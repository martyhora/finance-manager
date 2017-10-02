<?php

namespace App\Component;

use Nette;
use Model;

class ExpenseTypeForm extends \Nette\Application\UI\Control
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
        $this->template->setFile(__DIR__ . '/ExpenseFormType.latte');

        $this->template->render();
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new BootstrapForm();
        
        $form->addText('title', 'Název kategorie')
             ->setAttribute('class', 'form-control')
             ->addRule(BootstrapForm::FILLED, 'Je nutné zadat název.');
                
        $form->addSubmit('set', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = $this->expenseTypeFormSubmitted;

        return $form;
    }

    public function expenseTypeFormSubmitted(BootstrapForm $form, $values)
    {
        $this->expenseTypeRepository->save($values, $this->presenter->getParam('id'));
        /*if (!$this->isAjax()) {
            $this->redirect('this');
        }*/
        
        $this->presenter->flashMessage('Kategorie výdaje byla uložena.', 'success');
        $this->presenter->redirect('Expense:categoryList');
    }
}
