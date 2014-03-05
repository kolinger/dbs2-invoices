<?php

namespace App\Presenters;

use App\BootstrapForm;
use Nette\Forms\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class RegistrationPresenter extends BasePresenter
{


	/**
	 * @inject
	 * @var \App\Model\ManagersFacade
	 */
	public $managersFacade;


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$form = new BootstrapForm;

		$form->addText('username', 'Uživatelské jméno')
			->setRequired('Musíte vyplnit jméno');

		$form->addPassword('password', 'Heslo')
			->setRequired('Musíte vyplnit heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít nejméně 4 znaky', 4);

		$form->addPassword('verify', 'Ověření hesla')
			->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

		$form->addSubmit('send', 'Vytvořit účet');

		$form->onSuccess[] = callback($this, 'formSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 * @throws \PDOException
	 */
	public function formSubmitted(Form $form)
	{
		$values = $form->getValues();

		try {
			$this->managersFacade->create($values->username, $values->password);
			$this->user->login($values->username, $values->password);
			$this->resetSelectedCompany();
			$this->flashMessage('Váš účet byl úspěšně vytvořen, nyní si můžete vytvořit vaší společnost', 'success');
			$this->redirect('Companies:create');
		} catch (\PDOException $e) {
			if ($e->getCode() == 23505) {
				$form->addError('Zadané jméno je již obsazené, zvolte prosím jiné');
			} else {
				throw $e;
			}
		}
	}
}
