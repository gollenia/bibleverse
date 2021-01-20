<?php
/**
 * Tag Plugin, topic component: displays links to all wiki pages with a certain tag
 * 
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Thomas Gollenia <thomas@kids-team.at>
 */

use \dokuwiki\plugin\bibleverse\Utilities;

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');



/**
 * Topic syntax, displays links to all wiki pages with a certain tag
 */
class syntax_plugin_bibleverse extends DokuWiki_Syntax_Plugin {

    protected $special_pattern = '<bible\b[^>\r\n]*?/>';
    protected $entry_pattern   = '<bible\b.*?>(?=.*?</bible>)';
    protected $exit_pattern    = '</bible>';

    private string $verse = "";

    /**
     * @return string Syntax type
     */
    function getType(){ return 'formatting';}

    /**
     * @return string Paragraph type
     */
    function getPType() { return 'normal'; }

    /**
     * @return int Sort order
     */
    function getSort() { return 134; }

    function accepts($mode) {
        if ($mode == substr(get_class($this), 7)) return true;
        return parent::accepts($mode);
    }

    /**
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
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
    function handle($match, $state, $pos, Doku_Handler $handler){
        
        $data = explode(">",trim(substr($match, 6, -8)));
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
    function render($mode, Doku_Renderer $renderer, $data) {
        
        if ($mode == 'xhtml') {

            $renderer->info['cache'] = false;

            $verse = trim($data[0]);
            $link_text = trim($data[1]);
                    
            $query_array = explode(",", $verse);
            
            $query = new \dokuwiki\plugin\bibleverse\Model("bibel:" . $query_array[0], $query_array[1]);
            $query->query();
            $response = $query->get();

            $renderer->doc .= "<div class='uk-inline'><a href='#'>" . $link_text . "</a><div uk-drop class='uk-card uk-card-default'>";
            $renderer->doc .= "<div class='uk-card-header'><h5>" . $response['book']["title"] . " " . $response['chapter'] . "," . $query_array[1] . "</h5><span class='uk-text-small uk-text-muted'>Nach " . $response["translation"] . "</span></div><div class='uk-card-body'>";
            foreach($response["verses"] as $verse) {
                $renderer->doc .= "<span class='uk-text-small'><i class='uk-text-muted'>" . $verse->verse . "</i> " . $verse->text . "</span>";
            }

            $renderer->doc .= "</div><div class='uk-card-footer'><a class='uk-text-small' href='#'>Zum Kapitel</a></div></div></div>";          
            
            
            return true;

        }
        return false;
    }
}
