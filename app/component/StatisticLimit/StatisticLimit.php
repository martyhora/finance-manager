<?php

namespace App\Component;

use Model;
use Nette\Application\UI\Form;

class StatisticLimit extends \Nette\Application\UI\Control
{
    /**
     * @var Model\Expense
     */
    protected $expenseRepository;

    /**
     * @var Model\Limit
     */
    protected $limitRepository;

    /**
     * @var string
     */
    protected $statsData = [];

    public function __construct(Model\Expense $expenseRepository, Model\Limit $limitRepository)
    {
        parent::__construct();

        $this->expenseRepository     = $expenseRepository;
        $this->limitRepository = $limitRepository;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/StatisticLimit.latte');
        
        $month = date('n');
        $year  = date('Y');

        $this['limitFilterForm']->setDefaults(['month' => $month, 'year' => $year]);

        if (!$this->statsData) {
            $this->updateStatsData($year, $month);
        }

        $this->template->statsData = $this->statsData;

        $this->template->render();
    }

    protected function updateStatsData($year, $month)
    {
        $limit = $this->limitRepository->getLimit($year, $month)['limit'];
        $this->statsData['limit'] = $limit;

        if ($limit > 0) {
            $monthSpent = $this->expenseRepository->getSum($year, $month)->price_sum;
            $this->statsData['monthSpent'] = $monthSpent;

            $fixedExpenseSum = $this->expenseRepository->getSum($year, $month, true)->price_sum;
            $this->statsData['fixedExpenseSum'] = $fixedExpenseSum;

            $monthSpentExludingFixed = $monthSpent - $fixedExpenseSum;
            $this->statsData['monthSpentExludingFixed'] = $monthSpentExludingFixed;

            $percentage = round($monthSpent / $limit, 2) * 100;
            $this->statsData['percentage'] = $percentage;

            $limitExcludingFixed = $limit - $fixedExpenseSum;

            $percentageExcludingFixed = round($monthSpentExludingFixed / $limitExcludingFixed, 2) * 100;
            $this->statsData['percentageExcludingFixed'] = $percentageExcludingFixed;

            $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $actualDay = ($month == date('n') && $year == date('Y')) ? date('j') : $lastDay;
            $this->statsData['actualDay'] = $actualDay;
            
            $this->statsData['lastDay'] = $lastDay;

            $dayAvgLimitExcludingFixed = round($limitExcludingFixed / $lastDay);
            $this->statsData['dayAvgLimitExcludingFixed'] = $dayAvgLimitExcludingFixed;

            $dayAvgExcludingFixed = round($monthSpentExludingFixed / $actualDay);
            $this->statsData['dayAvgExcludingFixed'] = $dayAvgExcludingFixed;

            $avgTrend = $dayAvgLimitExcludingFixed * $actualDay;
            $this->statsData['avgTrend'] = $avgTrend;
            $avgPercentage = round($avgTrend / $limitExcludingFixed, 2) * 100;
            $this->statsData['avgPercentage'] = $avgPercentage;

            $this->statsData['spentDiff'] = $avgTrend - $monthSpentExludingFixed;
            // $this->statsData['spentDiffPercentage'] = $avgPercentage - $percentageExcludingFixed;

            $this->statsData['spentDiffAvg'] = $dayAvgLimitExcludingFixed - $dayAvgExcludingFixed;

            $this->statsData['spentDiffPercentageAvg'] = 0;

            if ($dayAvgExcludingFixed > 0) {
                $this->statsData['spentDiffPercentageAvg'] = round(100 - ($dayAvgLimitExcludingFixed / $dayAvgExcludingFixed) * 100) * ($dayAvgLimitExcludingFixed < $dayAvgExcludingFixed ? -1 : 1);
            }
        }
    }

    public function limitFilterFormSubmit()
    {
        $parameters = $this->presenter->getParameters();

        $this->updateStatsData($parameters['year'], $parameters['month']);
        $this->redrawControl('statisticsLimitDashboard');
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentLimitFilterForm()
    {
        $form = new Form();
        $form->setMethod(Form::GET);

        $months = range(1, 12);
        $years  = range(2013, date('Y'));
        
        $form->addSelect('month', 'Měsíc: ', array_combine($months, $months))->setAttribute('class', 'form-control');
        $form->addSelect('year', 'Rok: ', array_combine($years, $years))->setAttribute('class', 'form-control');
                        
        $form->addSubmit('filter_limit', ' Filtrovat ')->setAttribute('class', 'btn btn-primary btn-flat ajax');

        $form->onSuccess[] = [$this, 'limitFilterFormSubmit'];

        return $form;
    }
}
