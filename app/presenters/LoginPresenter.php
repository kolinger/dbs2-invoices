<?php

namespace App\Presenters;

use App\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class LoginPresenter extends BasePresenter
{

	/**
	 * @persistent
	 * @var string
	 */
	public $backlink;


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$form = new BootstrapForm;

		$form->addText('username', 'Uživatelské jméno')
			->setRequired('Musíte vyplnit jméno');

		$form->addPassword('password', 'Heslo')
			->setRequired('Musíte vyplnit heslo');

		$form->addSubmit('send', 'Přihlásit se');

		$form->onSuccess[] = callback($this, 'formSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 * @throws \Exception
	 */
	public function formSubmitted(Form $form)
	{
		$values = $form->getValues();

		try {
			$this->user->login($values->username, $values->password);
			$this->flashMessage('Přihlášení proběhlo úspěšně', 'success');
			if ($this->backlink) {
				$this->restoreRequest($this->backlink);
			} else {
				$this->redirect('Companies:');
			}
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
}
