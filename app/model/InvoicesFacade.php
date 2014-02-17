<?php

namespace App\Model;

use Nette\ArrayHash;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class InvoicesFacade extends Facade
{

	/**
	 * @param int $id
	 * @param int $userId
	 * @return bool|IRow
	 */
	public function findOneById($id, $userId)
	{
		return $this->createSelection($userId)->where('id', $id)->fetch();
	}


	/**
	 * @param int $offset
	 * @param int $limit
	 * @param int $userId
	 * @return array
	 */
	public function findAll($offset = 0, $limit = 20, $userId)
	{
		return $this->createSelection($userId)->limit($limit, $offset)->order('id DESC')->fetchAll();
	}


	/**
	 * @param int $id
	 * @return array
	 */
	public function findProductsById($id)
	{
		$items = $this->context->table('invoices_products')
			->where('invoice_id', $id)
			->fetchAll();

		$ids = array();
		$products = array();
		foreach ($items as $product) {
			$products[] = ArrayHash::from(array(
				'id' => $product->product_id,
				'price' => $product->price,
				'count' => $product->count,
				'tax' => $product->tax,
				'warranty' => $product->warranty,
			));
			$ids[] = $product->product_id;
		}

		$names = $this->context->table('products')->where('id', $ids)->fetchPairs('id', 'name');
		foreach ($products as $product) {
			$product->name = $names[$product->id];
		}

		return $products;
	}


	/**
	 * @param int $userId
	 * @return int
	 */
	public function count($userId)
	{
		return $this->createSelection($userId)->count('id');
	}


	/**
	 * @param int $companyId
	 * @param int $clientId
	 * @param string $type
	 * @param \DateTime $createDate
	 * @param \DateTime $endDate
	 * @param string $comment
	 * @param array $products
	 */
	public function create($companyId, $clientId, $type, $createDate, $endDate, $comment, array $products)
	{
		$values = array(
			'company_id' => $companyId,
			'client_id' => $clientId,
			'type' => $type,
			'create_date' => $createDate,
			'end_date' => $endDate,
			'comment' => $comment,
		);
		$id = $this->context->table('invoices')->insert($values);
		foreach ($products as $product) {
			$values = array(
				'invoice_id' => $id,
				'product_id' => $product->id,
				'price' => $product->price,
				'tax' => $product->tax,
				'count' => $product->count,
				'warranty' => $product->warranty,
			);
			$this->context->table('invoices_products')->insert($values);
		}
	}


	/**
	 * @param int $id
	 * @param int $userId
	 * @param int $clientId
	 * @param string $type
	 * @param \DateTime $createDate
	 * @param \DateTime $endDate
	 * @param string $comment
	 * @param array $products
	 */
	public function update($id, $userId, $clientId, $type, $createDate, $endDate, $comment, array $products)
	{
		$values = array(
			'client_id' => $clientId,
			'type' => $type,
			'create_date' => $createDate,
			'end_date' => $endDate,
			'comment' => $comment,
		);
		$this->context->query('UPDATE v_invoices SET ? WHERE id = ? AND manager_id = ?', $values, $id, $userId);

		$this->context->table('invoices_products')->where('invoice_id', $id)->delete();
		foreach ($products as $product) {
			$values = array(
				'invoice_id' => $id,
				'product_id' => $product->id,
				'price' => $product->price,
				'tax' => $product->tax,
				'count' => $product->count,
				'warranty' => $product->warranty,
			);
			$this->context->table('invoices_products')->insert($values);
		}
	}


	/**
	 * @param int $id
	 * @param int $userId
	 */
	public function delete($id, $userId)
	{
		$this->context->query('DELETE FROM v_invoices WHERE id = ? AND manager_id = ?', $id, $userId);
	}


	/**
	 * @param int $userId
	 * @return Selection
	 */
	private function createSelection($userId)
	{
		$query = $this->context->table('v_invoices');
		$query->where('manager_id', $userId);

		$company = $this->getSelectedCompany();
		if ($company) {
			$query->where('company_id', $company);
		}

		return $query;
	}
}