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

	public int $id = 0;
	public string $lang;
	public string $short_name;
	public string $long_name;
	public string $section;
	public int $order;
	public int $chapters;
	public string $testament;
	public string $translation;

	/**
	 * Construct new Book
	 *
	 * @param array $result Array filled wit data from the database
	 */
	public function __construct(array $result = [], $lang = 'en')
	{
		if (!$result) return;
		if (!array_key_exists('id', $result) || !$result["id"]) return;
		if (!empty($result)) {
			$this->id = $result['id'];
			$this->short_name = $result['short_name'];
			$this->long_name = $result['long_name'];
			$this->translation = '';
			$this->lang = $lang;
			$this->section = $result['section'];
			$this->chapters = $result['chapters'];
			$this->testament = $result['testament'];
		}
	}

	/**
	 * Find one book by its's numerical ID and create an instance of this class
	 *
	 * @param int $id
	 * @return Book
	 */
	public static function find(int $id, $lang = 'en')
	{
		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");
		$statement = $db->prepare("SELECT * FROM books WHERE id = :id ORDER BY 'id'");
		$statement->bindValue(':id', $id);

		$query = $statement->execute();
		$result = $query->fetchArray();

		if (!$result) {
			return new static([]);
		}
		return new static($result, $lang);
	}


	/**
	 * Get specified verse (or verses) of given chapter from the instances book
	 *
	 * @param integer $chapter
	 * @param string $verse comma separated, from to (with dash) or single verses (separated by ;)
	 * @return array Verses of given chapter
	 */
	public function verses(int $chapter, $verses = "")
	{
		$verses = Verse::where($this, $chapter, $verses);
		return $verses;
	}

	/**
	 * Find one or more books by key => value
	 * @TODO: Make this function failsave
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return array with Book instances
	 */
	public static function where($key, $value, $lang = 'en')
	{
		if (!in_array($key, ["id", "short_name", "long_name", "section", "order", "chapters", "testament"],)) {
			return false;
		}

		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");

		$statement = $db->prepare("SELECT * FROM books WHERE {$key} = :value ORDER BY 'id'");
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
			return [];
		}

		return $result;
	}

	public static function findByName(string $name, $lang = 'en'): self|bool
	{

		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");

		$statement = $db->prepare("SELECT * FROM books WHERE short_name = :value ORDER BY 'id'");

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
	public static function findAll($lang = 'en')
	{
		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");

		$query = $db->query("SELECT * FROM books");

		$result = [];
		while ($row = $query->fetchArray()) {
			$instance = new static($row, $lang);
			array_push($result, $instance);
		}

		return $result;
	}
}
