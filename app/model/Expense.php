<?php

namespace Model;

use Model;

class Expense extends Repository
{
    protected $tableName = 'expense';

    protected static $priorities = [1, 2, 3, 4, 5];

    protected $defaultOrder = ['date', 'DESC'];

    protected $filterColumns = [
        'like'  => ['title'],
        'equal' => ['priority'],
    ];

    /** @var Model\PeriodicPayment */
    protected $periodicPaymentRepository;

    public function __construct(\Nette\Database\Context $connection, Model\PeriodicPayment $periodicPaymentRepository)
    {
        $this->connection = $connection;
        $this->periodicPaymentRepository = $periodicPaymentRepository;
    }

    public static function getPriorities()
    {
        return self::$priorities;
    }
    
    public function findRows($filter, $order)
    {
        $filters = [];

        if (!empty($filter['date']['month'])) {
            $filters['MONTH(date)'] = $filter['date']['month'];
        }
        if (!empty($filter['date']['year'])) {
            $filters['YEAR(date)'] = $filter['date']['year'];
        }
        if (!empty($filter['day'])) {
            $filters['DAY(date)'] = $filter['day'];
        }
        if (!empty($filter['expense_type_title']['expense_type_id'])) {
            $filters['expense_type_id'] = $filter['expense_type_title']['expense_type_id'];
        }
        if (!empty($filter['periodic_payment_id'])) {
            $operator = $filter['periodic_payment_id'] == 1 ? '' : ' > ?';

            $filters['periodic_payment_id' . $operator] = 0;
        }

        $rows = parent::findRows($filter, $order);

        return $rows->where($filters);
    }
    
    public function findExpensesByMonth($year = null)
    {
        if (null === $year) {
            $year = date('Y');
        }
        
        $rows = $this->findBy(array('YEAR(date)' => $year))
                     ->select('*, SUM(price) AS price_sum, MONTH(date) AS month, YEAR(date) AS year')
                     ->group('MONTH(date)')
                     ->order('date DESC');
             
        return $rows;
    }

    public function getCategoriesStatsData(array $parameters)
    {
        $rows = $this->findAll()
                     ->select('SUM(price) AS price_sum, expense_type.title expense_type_title')
                     ->group('expense_type_id');

        if (!empty($parameters['date_start'])) {
            $rows->where('date >= ?', $parameters['date_start']);
        }

        if (!empty($parameters['date_end'])) {
            $rows->where('date <= ?', $parameters['date_end'] . ' 23:59:59');
        }

        if (!empty($parameters['type'])) {
            $rows->where('expense_type_id', $parameters['type']);
        }

        $result = [];

        foreach ($rows as $row) {
            $result[] = [$row->expense_type_title, $row->price_sum];
        }

        return $result;
    }

    public function getPriorityStatsData(array $parameters)
    {
        $rows = $this->findAll()
                     ->select('SUM(price) AS price_sum, priority')
                     ->group('priority');

        if (!empty($parameters['date_start'])) {
            $rows->where('date >= ?', $parameters['date_start']);
        }

        if (!empty($parameters['date_end'])) {
            $rows->where('date <= ?', $parameters['date_end'] . ' 23:59:59');
        }

        if (!empty($parameters['type'])) {
            $rows->where('expense_type_id', $parameters['type']);
        }

        $result = [];

        foreach ($rows as $row) {
            $result[] = ["{$row->priority}", $row->price_sum];
        }

        return $result;
    }

    public function getMonthlyStatsData()
    {
        $rows = $this->findAll()
                     ->select('SUM(price) AS price_sum, MONTH(date) month')
                     ->where('(date >= CONCAT(YEAR(CURDATE()) - 1, "-", MONTH(CURDATE()), "-1")) AND (date <= CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE()), "-", DAY(LAST_DAY(CURDATE()))))')
                     ->group('MONTH(date)');

        $result = [
            ["Měsíc", "Výdaje", ["role" => "style"]]
        ];

        foreach ($rows as $row) {
            $result[] = ["{$row->month}", $row->price_sum, "gold"];
        }

        return $result;
    }

    public function getStatsData(array $parameters)
    {
        return json_encode([
            'categories' => $this->getCategoriesStatsData($parameters),
            'priority'   => $this->getPriorityStatsData($parameters),
            'monthly'    => $this->getMonthlyStatsData(),
        ]);
    }

    public function getSum($year, $month, $onlyFixed = false)
    {
        $row = $this->findBy(['YEAR(date)' => $year, 'MONTH(date)' => $month])->select("SUM(price) price_sum");

        if ($onlyFixed) {
            $row->where('periodic_payment_id > 0');
        }

        return $row->fetch();
    }

    /**
     * Do měsíců, kde ještě nebyly zapsané pravidelné platby zapíše zadané pravidelné platby.
     *
     * @return [type] [description]
     */
    public function assignPeriodicPayments()
    {
        $periodicPayments = $this->periodicPaymentRepository->findAll()->where('NOW() >= date_start');

        foreach ($periodicPayments as $pp) {
            // zjistime, zda je již pravidelná platba na aktuální měsíc zapsána
            $expense = $this->findBy(['periodic_payment_id' => $pp->id, 'MONTH(date) = MONTH(CURDATE())', 'YEAR(date) = YEAR(CURDATE())'])->fetch();

            // pokud ano, přeskočíme
            if ($expense) {
                continue;
            }

            $this->save([
                'title'               => $pp->title,
                'expense_type_id'     => $pp->expense_type_id,
                'price'               => $pp->price,
                'priority'            => $pp->priority,
                'periodic_payment_id' => $pp->id,
                'date'                => date('Y-m') . '-' . sprintf("%02d", $pp->day),
            ]);
        }
    }
}
