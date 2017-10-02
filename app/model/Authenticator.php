<?php

namespace Model;

use Nette;
use Nette\Security;
use Nette\Security\Passwords;
use Model;

class Authenticator extends Nette\Object implements Security\IAuthenticator
{
    /** @var Model\User */
    private $userRepository;

    public function __construct(User $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->userRepository->findByName($username);

        if (!$row) {
            throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        }

        if (Passwords::verify($password, $row->password)) {
            throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        }

        $rowArray = $row->toArray();

        unset($rowArray['password']);
        return new Security\Identity($row->id, null, $rowArray);
    }

    public function setPassword($id, $password)
    {
        $this->userRepository->findBy(['id' => $id])->update([
            'password' => Passwords::hash($password),
        ]);
    }
}
