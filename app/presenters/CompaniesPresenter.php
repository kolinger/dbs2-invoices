<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class CompaniesPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\CompaniesFacade
	 */
	public $companiesFacade;

	/**
	 * @persistent
	 * @var string
	 */
	public $query;


	/************************ list ************************/


	public function renderDefault()
	{
		$paginator = new VisualPaginator($this, 'paginator');
		$paginator = $paginator->getPaginator();
		$paginator->itemCount = $this->companiesFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->companies = $this->companiesFacade->findAll($this->query, $paginator->offset, $paginator->itemsPerPage,
			$this->user->id);

		$this['filterForm']->setDefaults(array(
			'query' => $this->query,
		));
	}


	/**
	 * @return Form
	 */
	protected function createComponentFilterForm()
	{
		$form = new Form;

		$form->addText('query');

		$form->addSubmit('send', 'Hledat');

		$presenter = $this;
		$form->onSuccess[] = function (Form $form) use ($presenter) {
			$presenter->query = $form->getValues()->query;
			$presenter->redirect('this');
		};
		return $form;
	}

	/************************ form ************************/


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$form = new BootstrapForm;

		$form->addText('name', 'Název')
			->setRequired('Musíte vyplnit název');

		$form->addText('street', 'Ulice')
			->setRequired('Musíte vyplnit ulici');

		$form->addText('city', 'Město')
			->setRequired('Musíte vyplnit město');

		$form->addText('zip', 'PSČ')
			->setRequired('Musíte vyplnit PSČ')
			->addRule(Form::LENGTH, 'PSČ musí mít 5 číslic (bez mezery)', 5);

		$form->addText('companyIn', 'IČ')
			->setRequired('Musíte vyplnit IČ')
			->addRule(Form::LENGTH, 'Ič musí mít 8 znaků', 8);

		$form->addText('vatId', 'DIČ')
			->addCondition(Form::FILLED)
			->addRule(Form::LENGTH, 'DIČ musí mít 10 znaků', 10);

		$form->addTextArea('tradeRegister', 'Zapsáno')
			->setRequired('Musíte vyplnit kde je vaše společnost zapsána');

		$form->addText('email', 'E-mail');

		$form->addText('phone', 'Telefon');

		$form->addText('website', 'Webové stránky');

		$form->addText('bankAccount', 'Bankovní účet');

		$form->addTextArea('comment', 'Poznámka');

		$form->addSubmit('send', 'Uložit');

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

		$id = $this->getParameter('id');
		if ($id) {
			$this->companiesFacade->update($id, $this->user->id, $values->name, $values->street, $values->city,
				$values->zip, $values->tradeRegister, $values->companyIn, $values->vatId, $values->email,
				$values->phone, $values->website, $values->bankAccount, $values->comment);

			$this->flashMessage('Společnost byla upravena', 'success');
			$this->redirect('this');
		} else {
			$this->companiesFacade->create($this->user->id, $values->name, $values->street, $values->city, $values->zip,
				$values->tradeRegister, $values->companyIn, $values->vatId, $values->email, $values->phone,
				$values->website, $values->bankAccount, $values->comment);

			$this->flashMessage('Společnost byla vytvořena', 'success');
			$this->redirect('default');
		}
	}


	/************************ edit ************************/


	/**
	 * @param int $id
	 * @throws \Nette\Application\BadRequestException
	 */
	public function renderEdit($id)
	{
		$company = $this->companiesFacade->findOneById($id, $this->user->id);
		if (!$company) {
			throw new BadRequestException();
		}

		$this->template->company = $company;

		$this['form']->setDefaults(array(
			'name' => $company->name,
			'street' => $company->street,
			'city' => $company->city,
			'zip' => $company->zip,
			'tradeRegister' => $company->trade_register,
			'companyIn' => $company->company_in,
			'vatId' => $company->vat_id,
			'email' => $company->email,
			'phone' => $company->phone,
			'website' => $company->website,
			'bankAccount' => $company->bank_account,
			'comment' => $company->comment,
		));
	}


	/************************ delete ************************/


	/**
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		$this->companiesFacade->delete($id, $this->user->id);
		$this->flashMessage('Společnost byla úspěšně smazána.', 'success');
		$this->redirect('default');
	}


	/************************ switch ************************/


	/**
	 * @param int $id
	 */
	public function actionSwitch($id)
	{
		$settings = $this->session->getSection('settings');
		$settings->company = $id;
		$this->redirect('default');
	}
}
