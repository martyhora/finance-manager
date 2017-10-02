<?php

namespace App\Presenters;

class HomepagePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function createComponentStatistic()
    {
        return $this->context->getService('statistic');
    }

    public function createComponentStatisticLimit()
    {
        return $this->context->getService('statisticLimit');
    }
}
