<?php

namespace App\Presenters;

use Nette\Application\BadRequestException;

class LimitPresenter extends BasePresenter
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
        $form = $this['limitForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->limitRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }
            
            $form->setDefaults($row);
        }
    }
    
    public function actionDelete($id)
    {
        $this->limitRepository->delete($id);
        
        $this->flashMessage('Limit byl vymazán.', 'success');
        $this->redirect('Limit:');
    }
    
    protected function createComponentLimitForm()
    {
        return $this->context->getService('limitForm');
    }

    protected function createComponentLimitList()
    {
        return $this->context->getService('limitList');
    }
}
