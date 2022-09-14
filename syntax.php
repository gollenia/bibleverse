<?php

/**
 * Tag Plugin, topic component: displays links to all wiki pages with a certain tag
 * 
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Thomas Gollenia <thomas@kids-team.at>
 */


// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');



/**
 * Topic syntax, displays links to all wiki pages with a certain tag
 */
class syntax_plugin_bibleverse extends DokuWiki_Syntax_Plugin
{

	protected $special_pattern = '<bible\b[^>\r\n]*?/>';
	protected $entry_pattern   = '<bible\b.*?>(?=.*?</bible>)';
	protected $exit_pattern    = '</bible>';

	private string $verse = "";

	/**
	 * @return string Syntax type
	 */
	function getType()
	{
		return 'formatting';
	}


	/**
	 * @return int Sort order
	 */
	function getSort()
	{
		return 134;
	}

	function accepts($mode)
	{
		if ($mode == substr(get_class($this), 7)) return true;
		return parent::accepts($mode);
	}

	/**
	 * @param string $mode Parser mode
	 */
	function connectTo($mode)
	{
		$this->Lexer->addSpecialPattern('<bible.+?</bible>', $mode, 'plugin_bibleverse');
	}



	/**
	 * Handle matches of the topic syntax
	 *
	 * @param string $match The match of the syntax
	 * @param int    $state The state of the handler
	 * @param int    $pos The position in the document
	 * @param Doku_Handler    $handler The handler
	 * @return array Data for the renderer
	 */
	function handle($match, $state, $pos, Doku_Handler $handler)
	{

		$data = explode(">", trim(substr($match, 6, -8)));
		return $data;
	}

	/**
	 * Render xhtml output or metadata
	 *
	 * @param string         $mode      Renderer mode (supported modes: xhtml and metadata)
	 * @param Doku_Renderer  $renderer  The renderer
	 * @param array          $data      The data from the handler function
	 * @return bool If rendering was successful.
	 */
	function render($mode, Doku_Renderer $renderer, $data)
	{

		if ($mode == 'xhtml') {

			$renderer->info['cache'] = false;

			$verse = trim($data[0]);
			$link_text = trim($data[1]);

			$query_string = explode(",", $verse);
			$query_array = explode(":", $query_string[0]);

			$book = \dokuwiki\plugin\bibleverse\Book::where("short_name", $query_array[0]);
			if (!$book) {
				return false;
			}
			$verses = $book->get_verses($query_array[1], $query_string[1]);

			$renderer->doc .= "<span @mouseleave='showverse = false' @mouseenter='showverse = true' x-data='{showverse: false}'>";
			$renderer->doc .= "<a class='wikilink1' href='/bibel/{$query_array[0]}/{$query_array[1]}'>$link_text</a>";
			$renderer->doc .= "<span class='block rounded-tl-lg rounded-br-lg absolute p-4 bg-white max-w-sm shadow-lg' x-show='showverse'>";
			$renderer->doc .= "<span class='block'><span class='h5'>{$book->long_name} {$query_array[1]}</span><span class='block text-xs text-gray-500'>Nach {$book->translation}</span></span><span class='block pt-4 my-4 border-dotted border-t-2 border-gray-200'>";
			foreach ($verses as $verse) {
				$renderer->doc .= "<span class=''><sup class='text-gray-500'>{$verse->verse}</sup>{$verse->text}</span>";
			}
			$renderer->doc .= "</span></span></span>";


			return true;
		}
		return false;
	}
}
