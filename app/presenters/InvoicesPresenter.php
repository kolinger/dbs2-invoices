<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class InvoicesPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\InvoicesFacade
	 */
	public $invoicesFacade;
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
		$paginator->itemCount = $this->invoicesFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->invoices = $this->invoicesFacade->findAll($paginator->offset, $paginator->itemsPerPage,
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
		} else if ($id && !$companyId) {
			$invoice = $this->invoicesFacade->findOneById($id, $this->user->id);
			$companyId = $invoice->company_id;
		}

		if (isset($form['companyId'])) {
			$form->addDependentSelect('clientId', 'Zákazník', $form['companyId'], function (BaseControl $parent) use ($items) {
				if (!$parent->value) {
					reset($items);
					return $this->clientsFacade->findInPairs(key($items), $this->user->id);
				}
				return $this->clientsFacade->findInPairs($parent->value, $this->user->id);
			})->controlPrototype->data('dependent-select', 'true')->setRequired('Musíte vybrat zákazníka');
		} else {
			$items = $this->clientsFacade->findInPairs($companyId, $this->user->id);
			$form->addSelect('clientId', 'Zákazník', $items)
				->setRequired('Musíte vybrat zákazníka');
		}

		$items = array(
			'invoice' => 'Faktura',
			'credit_note' => 'Dobropis',
		);
		$form->addSelect('type', 'Typ', $items)
			->setRequired('Musíte vyplnit typ');

		$form->addDate('createDate', 'Datum vytvoření')
			->setRequired('Musíte vyplnit datum vytvoření');

		$form->addDate('endDate', 'Datum splatnosti')
			->setRequired('Musíte vyplnit datum splatnosti');

		$form->addTextArea('comment', 'Poznámka');

		$form->addSubmit('send', 'Uložit');

		if (isset($form['companyId'])) {
			$form->addSubmit('load', 'Load')
				->setValidationScope(FALSE)
				->controlPrototype->data('dependent-select-loader', 'true');
		}

		$form->onSuccess[] = callback($this, 'formSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 * @throws \PDOException
	 */
	public function formSubmitted(Form $form)
	{
		if (!$form['send']->isSubmittedBy()) {
			return;
		}

		$values = $form->getValues();

		$id = $this->getParameter('id');
		if ($id) {
			$this->invoicesFacade->update($id, $this->user->id, $values->clientId, $values->type, $values->createDate,
				$values->endDate, $values->comment);

			$this->flashMessage('Faktura byla upravena', 'success');
			$this->redirect('this');
		} else {
			$companyId = $this->getParameter('company');
			if ($companyId) {
				$values->companyId = $companyId;
			}
			$this->invoicesFacade->create($values->companyId, $values->clientId, $values->type, $values->createDate,
				$values->endDate, $values->comment);

			$this->flashMessage('Faktura byla vytvořena', 'success');
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
		$invoice = $this->invoicesFacade->findOneById($id, $this->user->id);
		if (!$invoice) {
			throw new BadRequestException();
		}

		$this->template->invoice = $invoice;

		$this['form']->setDefaults(array(
			'clientId' => $invoice->client_id,
			'type' => $invoice->type,
			'createDate' => $invoice->create_date,
			'endDate' => $invoice->end_date,
			'comment' => $invoice->comment,
		));

		if ($this->isAjax()) {
			$this->redrawControl('form');
		}
	}


	/************************ create ************************/


	public function renderCreate()
	{
		if ($this->isAjax()) {
			$this->redrawControl('form');
		}
	}


	/************************ delete ************************/


	/**
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		$this->invoicesFacade->delete($id, $this->user->id);
		$this->flashMessage('Faktura byla úspěšně smazána', 'success');
		$this->redirect('default');
	}
}
