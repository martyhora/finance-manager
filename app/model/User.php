<?php

namespace Model;

use Nette;

class User extends Repository
{
    protected $tableName = 'user';

    /**
     * @return Nette\Database\Table\ActiveRow
     */
    public function findByName($username)
    {
        return $this->findBy(array('username' => $username))->fetch();
    }
}
