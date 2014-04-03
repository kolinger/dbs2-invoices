<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class PaymentsPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\PaymentsFacade
	 */
	public $paymentsFacade;

	/**
	 * @inject
	 * @var \App\Model\InvoicesFacade
	 */
	public $invoicesFacade;

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
		$paginator->itemCount = $this->paymentsFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->payments = $this->paymentsFacade->findAll($paginator->offset, $paginator->itemsPerPage,
			$this->user->id);
	}


	/************************ form ************************/


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$form = new BootstrapForm;

		$id = $this->getParameter('id');
		if (!$id) {
			$form->addText('invoiceId', 'Variabilní symbol/číslo faktury')
				->setRequired('Musíte vyplnit variabilní symbol');
		}

		$form->addText('amount', 'Částka')
			->setRequired('Musíte vyplnit částku');

		$form->addDate('date', 'Datum')
			->setRequired('Musíte vyplnit datum');

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
			$this->paymentsFacade->update($id, $this->user->id, (float) $values->amount, $values->date, $values->comment);

			$this->flashMessage('Platba byla upravena', 'success');
			$this->redirect('this');
		} else {
			$invoice = $this->invoicesFacade->findOneById($values->invoiceId, $this->user->id);
			if (!$invoice) {
				$form->addError('K zadanému variabilnímu symbolu nebyla nalezena žádná platba');
				return;
			}

			$this->paymentsFacade->create($invoice->id, (float) $values->amount, $values->date,
				$values->comment);

			$this->flashMessage('Platba byla vytvořena', 'success');
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
		$payment = $this->paymentsFacade->findOneById($id, $this->user->id);
		if (!$payment) {
			throw new BadRequestException();
		}

		$this->template->payment = $payment;

		$this['form']->setDefaults(array(
			'amount' => $payment->amount,
			'date' => $payment->date,
			'comment' => $payment->comment,
		));
	}


	/************************ delete ************************/


	/**
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		$this->paymentsFacade->delete($id, $this->user->id);
		$this->flashMessage('Platba byla úspěšně smazána', 'success');
		$this->redirect('default');
	}
}
