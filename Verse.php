<?php

namespace dokuwiki\plugin\bibleverse;

use SQLite3;
use PDO;

/**
 * Bible Verse class
 * 
 * represents a single verse of a bible book in a chapter
 * 
 * @package bibleverse
 * @author Thomas Gollenia
 * @access 
 * @version 2.0
 */
class Verse
{

	public int $book = 0;
	public string $text = "";
	public int $chapter = 0;
	public int $verse = 0;
	public int $linebreak = 0;

	/**
	 * @param int $book 
	 * @param int $chapter 
	 * @param int $verse 
	 * @return void 
	 */
	public function __construct($result)
	{
		if ($result) {


			$this->text = $result['text'];
			$this->chapter = $result['chapter'];
			$this->verse = $result['verse'];
		}
	}



	/**
	 * Parses a verse query and returns a SQL compliant string of verses
	 *
	 * @param string $verses
	 * @return string 
	 */
	private static function parse_verse(string $verses)
	{

		if (strpos($verses, "-")) {
			$verses = explode("-", $verses);
			$verses = range(intval($verses[0]), intval($verses[1]));
			return implode(",", $verses);
		}

		if (strpos($verses, ";")) {
			$verses = explode(";", $verses);
			$sanitized_array = array_map('intval', $verses);
			return implode(",", $sanitized_array);
		}

		return implode([intval($verses)]);
	}

	static function where(Book $book, int $chapter, string $verse = "")
	{
		$verses = empty($verse) ? "" : "AND verse IN (" . self::parse_verse($verse) . ")";
		$lang = $book->lang;
		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");

		$statement = $db->prepare("SELECT * FROM verses WHERE (book_id = :book AND chapter = :chapter " . $verses . ") ORDER BY 'verse'");

		$statement->bindValue(':book', $book->id, SQLITE3_TEXT);
		$statement->bindValue(':chapter', $chapter, SQLITE3_TEXT);

		$query = $statement->execute();

		$result = [];

		while ($row = $query->fetchArray()) {

			$instance = new static($row);

			array_push($result, $instance);
		}
		return $result;
	}


	static function findAll(Book $book, int $chapter, $lang = "en")
	{
		$db = new SQLite3(__DIR__ . "/data/" . $lang . ".SQLite3");
		$statement = $db->prepare("SELECT * FROM verses WHERE (book_id = :book AND chapter = :chapter) ORDER BY 'verse'");

		$statement->bindValue(':book', $book->id, PDO::PARAM_INT);
		$statement->bindValue(':chapter', $chapter, PDO::PARAM_INT);

		$query = $statement->execute();

		$result = [];

		while ($row = $query->fetchArray()) {

			$instance = new static($row);

			array_push($result, $instance);
		}
		return $result;
	}
}
