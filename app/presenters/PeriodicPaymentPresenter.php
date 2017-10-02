<?php

namespace App\Presenters;

use Nette\Application\BadRequestException;

class PeriodicPaymentPresenter extends BasePresenter
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
        $form = $this['periodicPaymentForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->periodicPaymentRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }
            
            $form->setDefaults($row);
        }
    }
    
    public function actionDelete($id)
    {
        $this->periodicPaymentRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('PeriodicPayment:');
    }
    
    protected function createComponentPeriodicPaymentForm()
    {
        return $this->context->getService('periodicPaymentForm');
    }

    protected function createComponentPeriodicPaymentList()
    {
        return $this->context->getService('periodicPaymentList');
    }
}
