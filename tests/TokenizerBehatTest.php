<?php

namespace Tokenizer;


class TokenizerBehatTest extends \PHPUnit_Framework_TestCase {
    public $token_array;
    public $parent_key;
    public $search;
    public $token_array_two;

    public function setUp() {
        $this->search = 'foo is here but will it be replaced?';
        $this->token_array = array(
            'default' => array('foo' => 'bar')
        );
        $this->parent_key = 'default';
        $this->token_array_two = array(
            'default' => array('foo' => 'bar'),
            'foo2' => array('foo' => 'bar2', 'Submit' => 'Go')
        );
    }
    
    public function test_foo_replaced_and_search_keys() {
      $result = new TokenizerBehat($this->search, $this->token_array, $this->parent_key);
      $bar = $result->getReplaceKeys();
      $this->assertEquals('bar', $bar[0]);

      $foo = $result->getSearchKeys();
      $this->assertEquals('foo', $foo[0]);
      
    }

    public function test_foo_becomes_bar() {
      $result = new TokenizerBehat($this->search, $this->token_array, $this->parent_key);
      $this->assertEquals('bar is here but will it be replaced?', $result->getResults());
    }

    
    public function test_foo_becomes_bar2() {
      $result = new TokenizerBehat('Submit is here but will it be replaced?', $this->token_array_two, 'foo2');
      $this->assertEquals('Go is here but will it be replaced?', $result->getResults());
      
      $result = new TokenizerBehat('SubmitMe is here but will i be replaced?', $this->token_array, $this->parent_key);
      $this->assertEquals('SubmitMe is here but will i be replaced?', $result->getResults());
    }
    
    public function test_replace_keys_are_foo2_and_foo1() {

    }

    public function test_foo_becomes_foo2_not_foo1() {

    }

}
