<?php

namespace App\Model;

use Nette\Database\Table\IRow;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ManagersFacade extends Facade
{

	/**
	 * @inject
	 * @var \App\PasswordEncoder
	 */
	public $passwordEncoder;


	/**
	 * @param string $username
	 * @return bool|IRow
	 */
	public function findOneByUsername($username)
	{
		return $this->context->table('managers')->where('username', $username)->fetch();
	}


	/**
	 * @param int $username
	 * @param int $password
	 */
	public function create($username, $password)
	{
		$values = array(
			'username' => $username,
			'password' => $this->passwordEncoder->encode($password),
		);
		$this->context->query('INSERT INTO managers', $values);
	}
}