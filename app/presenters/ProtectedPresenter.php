<?php

namespace App\Presenters;

use Nette\Security\IUserStorage;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
abstract class ProtectedPresenter extends BasePresenter
{

	/**
	 * @param object $element
	 */
	public function checkRequirements($element)
	{
		if (!$this->user->loggedIn) {
			if ($this->user->logoutReason === IUserStorage::INACTIVITY) {
				$this->flashMessage('Byli jste automaticky odhlášení z důvodu delší neaktivity. Prosím přihlšte se znovu.', 'warn');
			} else {
				$this->flashMessage('Pro zobrazení stránky se musíte přihlásit', 'danger');
			}
			$this->redirect('Login:', array('backlink' => $this->storeRequest()));
		}
	}
}
