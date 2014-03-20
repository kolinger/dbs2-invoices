<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ProductsPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\ProductsFacade
	 */
	public $productsFacade;

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
		$paginator->itemCount = $this->productsFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->products = $this->productsFacade->findAll($paginator->offset, $paginator->itemsPerPage,
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

		$form->addText('count', 'Počet')
			->addCondition(Form::FILLED)
				->addRule(function ($control) { return preg_match('~^[0-9]+$~', $control->value); },
				'Počet musí být celé kladné číslo');

		$form->addText('price', 'Cena');

		$form->addText('tax', 'DPH')
			->addCondition(Form::FILLED)
				->addRule(function ($control) { return preg_match('~^[0-9]+$~', $control->value); },
				'Počet musí být celé kladné číslo');

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
			$this->productsFacade->update($id, $this->user->id, $values->name, $values->count, $values->price,
				$values->tax, $values->comment);

			$this->flashMessage('Produkt byl upraven', 'success');
			$this->redirect('this');
		} else {
			if (!isset($values->companyId)) {
				$values->companyId = $this->getSelectedCompany();
			}
			$this->productsFacade->create($values->companyId, $values->name, $values->count, $values->price,
				$values->tax, $values->comment);

			$this->flashMessage('Produkt byl vytvořen', 'success');
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
		$product = $this->productsFacade->findOneById($id, $this->user->id);
		if (!$product) {
			throw new BadRequestException();
		}

		$this->template->product = $product;

		$this['form']->setDefaults(array(
			'name' => $product->name,
			'count' => $product->count,
			'price' => $product->price,
			'tax' => $product->tax,
			'comment' => $product->comment,
		));
	}


	/************************ delete ************************/


	/**
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		$this->productsFacade->delete($id, $this->user->id);
		$this->flashMessage('Produkt byl úspěšně smazán', 'success');
		$this->redirect('default');
	}
}
