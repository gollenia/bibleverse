<?php

namespace dokuwiki\plugin\bibleverse;

use SQLite3;


/**
 * Represents a single book of the bible
 * 
 * @package bibleverse
 * @author Thomas Gollenia
 * @version 2.0
 */
class Book
{

	public int $id;
	public string $short_name;
	public string $long_name;
	public string $section;
	public int $order;
	public int $chapters;
	public string $testament;
	public string $number;

	/**
	 * Construct new Book
	 *
	 * @param array $result Array filled wit data from the database
	 */
	public function __construct(array $result = [])
	{
		if (!$result["id"]) return;
		if (!empty($result)) {
			$this->id = $result['id'];
			$this->short_name = $result['short_name'];
			$this->long_name = $result['long_name'];
			$this->section = $result['section'];
			$this->order = $result['order'];
			$this->chapters = $result['chapters'];
			$this->testament = $result['testament'];
			$this->number = $result['number'];
		}
	}

	/**
	 * Find one book by its's numerical ID and create an instance of this class
	 *
	 * @param int $id
	 * @return Book
	 */
	public static function find(int $id)
	{
		$db = new SQLite3(__DIR__ . "/data/schl51.SQLite3");

		$statement = $db->prepare("SELECT * FROM books WHERE id = :id ORDER BY 'order'");
		$statement->bindValue(':value', $id);

		$query = $statement->execute();
		$result = $query->fetchArray();
		if (!empty($result)) {
			$instance = new static($result);
		}
		return $instance;
	}

	/**
	 * Get specified verse (or verses) of given chapter from the instances book
	 *
	 * @param integer $chapter
	 * @param string $verse comma separated, from to (with dash) or single verses (separated by ;)
	 * @return array Verses of given chapter
	 */
	public function get_verses(int $chapter, $verse = "")
	{
		$verses = Verse::where($this, $chapter, $verse);
		return $verses;
	}

	/**
	 * Find book by key => value
	 * @TODO: Make this function failsave
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return array with Book instances
	 */
	public static function where($key, $value)
	{
		if (!in_array($key, ["id", "short_name", "long_name", "section", "order", "chapters", "testament"],)) {
			return false;
		}

		$db = new SQLite3(__DIR__ . "/data/schl51.SQLite3");

		$statement = $db->prepare("SELECT * FROM books WHERE {$key} = :value ORDER BY 'order'");
		$statement->bindValue(':value', $value);

		$query = $statement->execute();
		$result = [];

		if ($query->numColumns() == 1) {
			$instance = new static($query->fetchArray());
			return $instance;
		}
		while ($row = $query->fetchArray()) {
			$instance = new static($row);
			array_push($result, $instance);
		}

		if (count($result) == 1) {
			return $result[0];
		}

		if (count($result) == 0) {
			return false;
		}

		return $result;
	}

	public static function findByName(string $name): self|bool
	{
		$db = new SQLite3(__DIR__ . "/data/schl51.SQLite3");

		$statement = $db->prepare("SELECT * FROM books WHERE short_name = :value ORDER BY 'order'");

		$statement->bindValue(':value', $name);

		$query = $statement->execute();
		$result = $query->fetchArray();
		if ($result) {
			$instance = new static($result);
			return $instance;
		}
		return false;
	}

	/**
	 * Get all Book
	 *
	 * @return array with Book instances
	 */
	public static function findAll()
	{
		$db = new SQLite3(__DIR__ . "/data/schl51.SQLite3");

		$query = $db->query("SELECT * FROM books");

		$result = [];
		while ($row = $query->fetchArray()) {
			$instance = new static($row);
			array_push($result, $instance);
		}

		return $result;
	}
}
