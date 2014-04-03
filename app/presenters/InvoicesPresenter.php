<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
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

	/**
	 * @inject
	 * @var \App\Model\ProductsFacade
	 */
	public $productsFacade;

	/**
	 * @persistent
	 * @var string
	 */
	public $query;

	/**
	 * @persistent
	 * @var int
	 */
	public $state;


	/************************ list ************************/


	public function renderDefault()
	{
		$paginator = new VisualPaginator($this, 'paginator');
		$paginator = $paginator->getPaginator();
		$paginator->itemCount = $this->invoicesFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->invoices = $this->invoicesFacade->findAll($this->query, $this->state, $paginator->offset,
			$paginator->itemsPerPage, $this->user->id);

		$this['filterForm']->setDefaults(array(
			'query' => $this->query,
			'state' => $this->state,
		));
	}


	/**
	 * @return Form
	 */
	protected function createComponentFilterForm()
	{
		$form = new Form;

		$form->addText('query');

		$form->addSelect('state', NULL, array(
			'- vyberte stav -',
			'Zaplaceno',
			'Nezaplaceno',
		));

		$form->addSubmit('send', 'Hledat');

		$presenter = $this;
		$form->onSuccess[] = function (Form $form) use ($presenter) {
			$presenter->query = $form->getValues()->query;
			$presenter->state = $form->getValues()->state;
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

		$companyId = $this->getParameter('company');
		if (!$companyId) {
			$companyId = $this->getSelectedCompany();
		}

		$id = $this->getParameter('id');
		if (!$companyId && !$id) {
			$items = $this->permissionsFacade->findAvailableCompaniesInPairs($this->user->id);
			$form->addSelect('companyId', 'Společnost', $items)
				->setRequired('Musíte vybrat společnost')
				->controlPrototype->data('dependent-select', 'true');

			$form->addDependentSelect('clientId', 'Zákazník', $form['companyId'], function (BaseControl $parent) use ($items) {
				if (!$parent->value) {
					reset($items);
					return $this->clientsFacade->findInPairs(key($items), $this->user->id);
				}
				return $this->clientsFacade->findInPairs($parent->value, $this->user->id);
			})->setRequired('Musíte vybrat zákazníka');
		} else {
			if ($id) {
				$companyId = $this->invoicesFacade->findOneById($id, $this->user->id)->company_id;
			}
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

		$products = json_encode($this->productsFacade->findNames($this->user->id));
		$form->addDynamic('products', function ($product) use ($products) {
			$product->addText('count', 'Počet')
				->setRequired('Musíte vyplnit počet')
				->addRule(function ($control) { return preg_match('~^[0-9]+$~', $control->value); },
					'Počet musí být celé kladné číslo');

			$product->addText('price', 'Cena')
				->setRequired('Musíte vyplnit cenu');

			$product->addText('tax', 'DPH')
				->setRequired('Musíte vyplnit DPH')
				->addRule(function ($control) { return preg_match('~^[0-9]+$~', $control->value); },
					'DPH musí být celé kladné číslo');

			$product->addText('warranty', 'Záruka (měsíce)')
				->setRequired('Musíte vyplnit záruku (zadejte 0 pro žádnou záruku)')
				->addRule(function ($control) { return preg_match('~^[0-9]+$~', $control->value); },
					'Záruka musí být celé kladné číslo');

			$product->addSubmit('remove', 'Odebrat')
				->addRemoveOnClick();

			$product->addHidden('id');
			$product->addHidden('name');
		});

		$items = $this->productsFacade->findInPairs($this->user->id);
		$form['products']->addSelect('product', 'Produkt', $items);

		$form['products']->addSubmit('add', 'Přidat vybraný produkt')
			->setValidationScope(FALSE)
			->addCreateOnClick(function ($replicator, $user) {
				$id = $replicator['product']->getValue();
				if ($id) {
					$product = $this->productsFacade->findOneById($id, $this->user->id);
					$user['count']->setValue(1);
					$user['price']->setValue($product->price);
					$user['tax']->setValue($product->tax);
					$user['warranty']->setValue($product->warranty);
					$user['name']->setValue($product->name);
					$user['id']->setValue($id);
				}
			});

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

		$products = array();
		foreach ($values->products as $product) {
			if (!is_scalar($product)) {
				$products[] = $product;
			}
		}

		if (!count($products)) {
			$form->addError('Musíte přidat alespoň jeden produkt');
			return;
		}

		$id = $this->getParameter('id');
		if ($id) {
			$this->invoicesFacade->update($id, $this->user->id, $values->clientId, $values->type, $values->createDate,
				$values->endDate, $values->comment, $products);

			$this->flashMessage('Faktura byla upravena', 'success');
			$this->redirect('this');
		} else {
			if (!isset($values->companyId)) {
				$values->companyId = $this->getSelectedCompany();
			}

			$this->invoicesFacade->create($values->companyId, $values->clientId, $values->type, $values->createDate,
				$values->endDate, $values->comment, $products);

			$this->flashMessage('Faktura byla vytvořena', 'success');
			$this->redirect('default');
		}
	}


	/************************ edit ************************/


	/**
	 * @param int $id
	 * @throws BadRequestException
	 */
	public function renderEdit($id)
	{
		$invoice = $this->invoicesFacade->findOneById($id, $this->user->id);
		if (!$invoice) {
			throw new BadRequestException();
		}

		$this->template->invoice = $invoice;

		$form = $this['form'];
		$form->setDefaults(array(
			'companyId' => $invoice->company_id,
			'clientId' => $invoice->client_id,
			'type' => $invoice->type,
			'createDate' => $invoice->create_date,
			'endDate' => $invoice->end_date,
			'comment' => $invoice->comment,
		));
		if (!$form->isSubmitted()) {
			$number = 0;
			$products = $this->invoicesFacade->findProductsById($id);
			foreach ($products as $product) {
				$form['products'][++$number]->setValues(array(
					'name' => $product->name,
					'price' => $product->price,
					'id' => $product->id,
					'tax' => $product->tax,
					'count' => $product->count,
					'warranty' => $product->warranty,
				));
			}
		}

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


	/************************ print ************************/


	/**
	 * @param int $id
	 * @throws BadRequestException
	 */
	public function renderPrint($id)
	{
		$invoice = $this->invoicesFacade->findOneById($id, $this->user->id);
		if (!$invoice) {
			throw new BadRequestException();
		}
		$this->template->invoice = $invoice;
		$this->template->products = $this->invoicesFacade->findProductsById($id);
		$this->template->company = $this->companiesDao->findOneById($invoice->company_id, $this->user->id);
		$this->template->client = $this->clientsFacade->findOneById($invoice->client_id, $this->user->id);
	}
}
