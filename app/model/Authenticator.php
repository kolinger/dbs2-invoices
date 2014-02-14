<?php
namespace App\Model;

use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;

/**
 * @author Tomáš Kolinger <tomas@kolinger.name>
 */
class Authenticator extends Object implements IAuthenticator
{

	/**
	 * @inject
	 * @var \App\Model\ManagersFacade
	 */
	public $managersFacade;

	/**
	 * @inject
	 * @var \App\PasswordEncoder
	 */
	public $passwordEncoder;


	/**
	 * @param array $credentials
	 * @return Identity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$manager = $this->managersFacade->findOneByUsername($username);

		if (!$manager) {
			throw new AuthenticationException('Uživatelské jméno nebylo nalezeno', IAuthenticator::IDENTITY_NOT_FOUND);
		}

		if (!$this->passwordEncoder->matches($password, $manager->password)) {
			throw new AuthenticationException('Zadané heslo nesouhlasí', IAuthenticator::INVALID_CREDENTIAL);
		}

		return new Identity($manager->id, array(), array('name' => $manager->username));
	}
}