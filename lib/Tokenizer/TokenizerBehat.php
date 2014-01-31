<?php

namespace Tokenizer;

use Symfony\Component\Config\Definition\Exception\Exception;
use Tokenizer\TokenizerModel;

/**
 * Class TokenizerBehat
 * @package TokenizerBehat
 *
 * Takes the Tokenizer token array file and
 * compares it to an string / array of content
 * replaces the values in that string / array
 * with the values in the token
 *
 * e.g.
 * $sting = 'foo';
 * $token_array = array('default' => array('foo' => 'bar'))
 *
 * $string will return bar since there is a matching foo
 *
 *
 * @param string | array to check
 * @param array from Tokenizer Class
 * @param string Parent key to start from default is default and
 *   will be inherited so if the token array is
 *   array('default' => array('foo' => 'bar'), 'foo2' => array('foo' => 'bar2', 'foo3' => 'bar3'))
 *   and foo2 is the Parent key being passed then the tokens to look for
 *   will be those of default eg bar plus those of foo2 eg bar2 and bar3
 *
 *
 */

class TokenizerBehat {
    public $search;
    public $token_array;
    public $parent_key;
    public $search_keys;
    public $replace_keys;
    protected $array_flattened;
    protected $results;

    public function __construct($search, $token_array, $parent_key = 'default') {
        $this->search = $search;
        $this->token_array = $token_array;
        $this->parent_key = $parent_key;
        $this->search_keys = $this->getSearchKeys();
        $this->replace_keys = $this->getReplaceKeys();
    }

    public function getReplaceKeys() {
        return $this->setReplaceKeys();
    }

    public function getSearchKeys() {
        return $this->setSearchKeys();
    }

    public function getResults() {
      return $this->findAndReplace();
    }

    public function findAndReplace() {
        $this->results = str_replace($this->search_keys, $this->replace_keys, $this->search);
        return $this->results;
    }

    protected function setSearchKeys() {
        $this->checkKeys();
        $this->search_keys = $this->token_array['default'];
        if($this->parent_key != 'default') {
            $this->search_keys = array_merge($this->search_keys, $this->token_array[$this->parent_key]);
        }
        $this->search_keys = array_keys($this->search_keys);
	return $this->search_keys;
    }

    protected function setReplaceKeys() {
        $this->checkKeys();
        $this->replace_keys = $this->token_array['default'];
        if($this->parent_key != 'default') {
            $this->replace_keys = array_merge($this->replace_keys, $this->token_array[$this->parent_key]);
        }
	$this->replace_keys = array_values($this->replace_keys);
        return $this->replace_keys;
    }

    protected function checkKeys() {
        if(!isset($this->token_array[$this->parent_key])) {
            throw new \Exception(sprintf('This token array does not have this key %s', $this->parent_key));
        }
    }


}
