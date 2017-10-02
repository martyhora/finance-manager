<?php

namespace App\Component;

use Nette;
use Model;

class LimitForm extends \Nette\Application\UI\Control
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
        $this->template->setFile(__DIR__ . '/LimitForm.latte');

        $this->template->render();
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new BootstrapForm();
        
        $form->addText('limit', 'Výše limitu')
             ->setAttribute('class', 'form-control')
             ->addRule(BootstrapForm::FILLED, 'Je nutné zadat výši limitu.');

        $months = range(1, 12);
        $years  = range(2013, date('Y'));
        
        $form->addSelect('month', 'Měsíc: ', array_combine($months, $months))->setAttribute('class', 'form-control');
        $form->addSelect('year', 'Rok: ', array_combine($years, $years))->setAttribute('class', 'form-control');

        $form->setDefaults(['month' => date('n'), 'year' => date('Y')]);
                
        $form->addSubmit('set', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = $this->limitTypeFormSubmitted;

        return $form;
    }

    public function limitTypeFormSubmitted(BootstrapForm $form, $values)
    {
        $this->limitRepository->save($values, $this->presenter->getParam('id'));
        /*if (!$this->isAjax()) {
            $this->redirect('this');
        }*/
        
        $this->presenter->flashMessage('Limit byl uložen.', 'success');
        $this->presenter->redirect('Limit:');
    }
}
