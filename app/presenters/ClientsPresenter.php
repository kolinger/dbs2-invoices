<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ClientsPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\ClientsFacade
	 */
	public $clientsFacade;

	/**
	 * @inject
	 * @var \App\Model\PermissionsFacade
	 */
	public $permissionsFacade;


	/************************ list ************************/


	public function renderDefault()
	{
		$paginator = new VisualPaginator($this, 'paginator');
		$paginator = $paginator->getPaginator();
		$paginator->itemCount = $this->clientsFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->clients = $this->clientsFacade->findAll($paginator->offset, $paginator->itemsPerPage,
			$this->user->id);
	}


	/************************ form ************************/


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$form = new BootstrapForm;

		$companyId = $this->getParameter('company');
		if (!$companyId) {
			$companyId = $this->getSelectedCompany();
		}

		$id = $this->getParameter('id');
		if (!$companyId && !$id) {
			$items = $this->permissionsFacade->findAvailableCompaniesInPairs($this->user->id);
			$form->addSelect('companyId', 'Společnost', $items)
				->setRequired('Musíte vybrat společnost');
		}

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
			->addCondition(Form::FILLED)
			->addRule(Form::LENGTH, 'Ič musí mít 8 znaků', 8);

		$form->addText('vatId', 'DIČ')
			->addCondition(Form::FILLED)
			->addRule(Form::LENGTH, 'DIČ musí mít 10 znaků', 10);

		$form->addText('email', 'E-mail');

		$form->addText('phone', 'Telefon');

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
			$this->clientsFacade->update($id, $this->user->id, $values->name, $values->street, $values->city,
				$values->zip, $values->companyIn, $values->vatId, $values->email, $values->phone, $values->comment);

			$this->flashMessage('Klient byl upraven', 'success');
			$this->redirect('this');
		} else {
			$companyId = $this->getSelectedCompany();
			if ($companyId) {
				$values->companyId = $companyId;
			}
			$this->clientsFacade->create($values->companyId, $values->name, $values->street, $values->city,
				$values->zip, $values->companyIn, $values->vatId, $values->email, $values->phone, $values->comment);

			$this->flashMessage('Klient byl vytvořen', 'success');
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
		$client = $this->clientsFacade->findOneById($id, $this->user->id);
		if (!$client) {
			throw new BadRequestException();
		}

		$this->template->client = $client;

		$this['form']->setDefaults(array(
			'name' => $client->name,
			'street' => $client->street,
			'city' => $client->city,
			'zip' => $client->zip,
			'companyIn' => $client->company_in,
			'vatId' => $client->vat_id,
			'email' => $client->email,
			'phone' => $client->phone,
			'comment' => $client->comment,
		));
	}


	/************************ delete ************************/


	/**
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		$this->clientsFacade->delete($id, $this->user->id);
		$this->flashMessage('Klient byl úspěšně smazán', 'success');
		$this->redirect('default');
	}
}
