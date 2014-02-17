<?php

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
abstract class BasePresenter extends Presenter
{

	/**
	 * @inject
	 * @var \App\Model\CompaniesFacade
	 */
	public $companiesDao;


	public function beforeRender()
	{
		if ($this->user->loggedIn) {
			$this['companyForm']->setDefaults(array(
				'company' => $this->getSelectedCompany(),
			));
		}
	}


	/**
	 * @return Form
	 */
	protected function createComponentCompanyForm()
	{
		$form = new Form();

		$items = $this->companiesDao->findMineInPairs($this->user->id);
		$form->addSelect('company', 'Vybraná společnost:', $items)
			->setPrompt('- všechny společnosti -');

		$form->addSubmit('send');

		$form->onSuccess[] = callback($this, 'companyFormSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function companyFormSubmitted(Form $form)
	{
		$values = $form->getValues();
		$settings = $this->session->getSection('settings');
		$settings->company = $values->company;
		$this->redirect('this');
	}


	/**
	 * @return int|NULL
	 */
	public function getSelectedCompany()
	{
		$settings = $this->session->getSection('settings');
		return $settings && isset($settings->company) ? $settings->company : NULL;
	}
}
