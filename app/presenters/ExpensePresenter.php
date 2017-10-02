<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Model;

class ExpensePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function renderEdit($id)
    {
        $form = $this['expenseForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->expenseRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }
            
            $form->setDefaults($row);
        }
    }
    
    public function actionDelete($id)
    {
        $this->expenseRepository->delete($id);
        
        $this->flashMessage('Výdaj byl vymazán.', 'success');
        $this->redirect('default');
    }
    
    public function createComponentCategoryList()
    {
        return $this->context->getService('categoryList');
    }

    public function renderEditType($id)
    {
        $form = $this['expenseTypeForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->expenseTypeRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }
            
            $form->setDefaults($row);
        }
    }
    
    public function actionDeleteType($id)
    {
        $this->expenseTypeRepository->delete($id);
        
        $this->flashMessage('Kategorie byla vymazána.', 'success');
        $this->redirect('categoryList');
    }

    protected function createComponentExpenseList()
    {
        return $this->context->getService('expenseList');
    }

    protected function createComponentExpenseForm()
    {
        return $this->context->getService('expenseForm');
    }

    protected function createComponentExpenseTypeForm()
    {
        return $this->context->getService('expenseTypeForm');
    }
    
    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentExpenseFilterForm()
    {
        $form = new Form();
        $form->setMethod(Form::GET);
        
        $typePairs = $this->expenseTypeRepository->findAll()->fetchPairs('id', 'title');
        
        $form->addSelect('type', 'Typ výdaje', $typePairs)
             ->setPrompt('- Vyberte -')
             ->setAttribute('class', 'form-control')
             ->setAttribute('onchange', 'this.form.submit()');
             
        $form->addCheckbox('group_type', ' Seskupit podle typu')
             ->setAttribute('onchange', 'this.form.submit()');
        
        $form->addSelect('month', 'Měsíc', array_combine(range(1, 12), range(1, 12)))
             ->setAttribute('style', 'width: 70px;')
             ->setAttribute('class', 'form-control');
        
        $form->addSelect('year', 'rok', array_combine(range(2012, date('Y')), range(2012, date('Y'))))
             ->setAttribute('style', 'width: 80px;')
             ->setAttribute('class', 'form-control');

        $form->addSelect('priority', 'Priorita', array_combine(Model\Expense::getPriorities(), Model\Expense::getPriorities()))
             ->setPrompt('-')
             ->setAttribute('style', 'width: 60px; display: inline')
             ->setAttribute('class', 'form-control');

        $form->addSelect('fixed', 'Fixní', ['ne', 'ano'])
             ->setPrompt('-')
             ->setAttribute('style', 'width: 80px; display: inline')
             ->setAttribute('class', 'form-control');
                        
        $form->addSubmit('filter', ' Filtrovat ')->setAttribute('class', 'btn btn-primary btn-flat');

        return $form;
    }
}
