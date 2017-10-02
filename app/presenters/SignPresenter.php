<?php

namespace App\Presenters;

use Nette;

use Nette\Application\UI;

class SignPresenter extends BasePresenter
{
    protected function createComponentSignInForm()
    {
        $form = new UI\Form;
        $form->addText('username', 'Username:')
             ->setAttribute('class', 'form-control')
             ->setRequired('Vyplňte prosím uživatelské jméno.');

        $form->addPassword('password', 'Password:')
             ->setAttribute('class', 'form-control')
             ->setRequired('Vyplňte prosím heslo.');

        $form->addSubmit('send', 'Přihlásit se')->setAttribute('style', 'width: 100%')->setAttribute('class', 'btn btn-primary btn-flat');

        // call method signInFormSubmitted() on success
        $form->onSuccess[] = $this->signInFormSubmitted;
        return $form;
    }



    public function signInFormSubmitted($form)
    {
        $values = $form->getValues();

        // if ($values->remember) {
        // 	$this->getUser()->setExpiration('+ 14 days', FALSE);
        // } else {
        // 	$this->getUser()->setExpiration('+ 20 minutes', TRUE);
        // }

        try {
            $this->getUser()->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->redirect('Homepage:');
    }



    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.');
        $this->redirect('in');
    }
}
