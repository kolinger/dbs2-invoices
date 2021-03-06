<?php

namespace App\Model;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ProductsFacade extends Facade
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
	 * @param string $query
	 * @param int $offset
	 * @param int $limit
	 * @param int $userId
	 * @return array
	 */
	public function findAll($query, $offset = 0, $limit = 20, $userId)
	{
		$selection = $this->createSelection($userId)->limit($limit, $offset);

		if ($query) {
			$query = '%' . $query . '%';
			$selection->where('(
				company_name LIKE ? OR
				name LIKE ? OR
				CAST(price AS text) LIKE ? OR
				CAST(tax AS text) LIKE ?
				)', $query, $query, $query, $query
			);
		}

		return $selection->fetchAll();
	}


	/**
	 * @param int $userId
	 * @return array
	 */
	public function findInPairs($userId)
	{
		return $this->createSelection($userId)->fetchPairs('id', 'name');
	}


	/**
	 * @param int $userId
	 * @return array
	 */
	public function findNames($userId)
	{
		$products = $this->createSelection($userId)->fetchAll();
		$names = array();
		foreach ($products as $product) {
			$names[] = $product->name;
		}
		return $names;
	}


	/**
	 * @param int $userId
	 * @return int
	 */
	public function count($userId)
	{
		return $this->createSelection($userId)->count();
	}


	/**
	 * @param int $companyId
	 * @param string $name
	 * @param float $price
	 * @param int $tax
	 * @param string $comment
	 */
	public function create($companyId, $name, $price, $tax, $comment)
	{
		$values = array(
			'company_id' => $companyId,
			'name' => $name,
			'price' => $price == '' ? NULL : $price,
			'tax' => $tax == '' ? NULL : $tax,
			'comment' => $comment,
		);
		$this->context->table('products')->insert($values);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 * @param string $name
	 * @param float $price
	 * @param int $tax
	 * @param string $comment
	 */
	public function update($id, $userId, $name, $price, $tax, $comment)
	{
		$values = array(
			'name' => $name,
			'price' => $price == '' ? NULL : $price,
			'tax' => $tax == '' ? NULL : $tax,
			'comment' => $comment,
		);
		$this->context->query('UPDATE v_products SET ? WHERE id = ? AND manager_id = ?', $values, $id, $userId);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 */
	public function delete($id, $userId)
	{
		$this->context->query('DELETE FROM v_products WHERE id = ? AND manager_id = ?', $id, $userId);
	}


	/**
	 * @param int $userId
	 * @return Selection
	 */
	private function createSelection($userId)
	{
		$query = $this->context->table('v_products');
		$query->where('manager_id', $userId);

		$company = $this->getSelectedCompany();
		if ($company) {
			$query->where('company_id', $company);
		}

		return $query;
	}
}