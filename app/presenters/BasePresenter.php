<?php

namespace App\Presenters;

use Nette;
use Model;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Model\Expense */
    protected $expenseRepository;
    
    /** @var Model\ExpenseType */
    protected $expenseTypeRepository;

    /** @var Model\Limit */
    protected $limitRepository;

    /** @var Model\PeriodicPayment */
    protected $periodicPaymentRepository;

    public function __construct(Model\Expense $expenseRepository, Model\ExpenseType $expenseTypeRepository, Model\Limit $limitRepository, Model\PeriodicPayment $periodicPaymentRepository)
    {
        parent::__construct();

        $this->expenseRepository     = $expenseRepository;
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->limitRepository       = $limitRepository;
        $this->periodicPaymentRepository = $periodicPaymentRepository;
    }
    
    public function beforeRender()
    {
        parent::beforeRender();
        
        $this->template->expensesMonth = $this->expenseRepository->findExpensesByMonth();
        
        $this->expenseRepository->assignPeriodicPayments();
    }
}
