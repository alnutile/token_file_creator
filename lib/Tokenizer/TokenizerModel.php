<?php

namespace Tokenizer;


use Symfony\Component\Finder\Finder,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Config,
    Symfony\Component\Serializer\Serializer,
    Symfony\Component\Yaml\Yaml;


class TokenizerModel {
    protected $test_filename;
    protected $token_filename;
    protected $fullpath_to_test_no_filename;
    protected $filesystem;
    protected $ymal_parser;
    protected $token_string;
    protected $token_content = array();
    const ROOT_TOKEN_FOLDER = 'tokens';
	
    public function __construct($test_filename, $fullpath_to_test_no_filename, $token_content = array(), Filesystem $filesystem, Yaml $ymal_parser) {
        $this->test_filename = $test_filename;
        $this->token_content = $token_content;
        $this->token_filename = $test_filename . '.token';
        $this->fullpath_to_test_no_filename = $fullpath_to_test_no_filename;
        $this->filesystem = $filesystem;
        $this->ymal_parser = $ymal_parser;
    }

    public function create() {
        $check = $this->checkRoot();
        if(!$check) {
            try {
                $this->setRoot();
            } catch (\Exception $e) {
                return array('errors' => 1, 'message' => $e->getMessage());
            }
        }
        $check_for_file = $this->checkForFile();
        if($check_for_file) {
            return array('errors' => 1, 'message' => "File exists already");
        }
        try {
            $this->checkArrayFormat();
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => $e->getMessage());
        }
        $yml = $this->convertArrayToTokenFile();
        try {
            $this->store();
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => "could not write to file {$e->getMessage()}");
        }

        return array('errors' => 0, 'message' => "File saved");
    }

    public function update() {
        //1. passing incoming content to yml dump
        $this->convertArrayToTokenFile();
        //2. write the output of that (if it is good) to a file
        try {
            $this->store();
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => "could not write to file {$e->getMessage()}");
        }
    }

    public function retrieve() {
        $check_for_file = $this->checkForFile();
        if(!$check_for_file) {
            return array('errors' => 1, 'message' => "File is missing please create one");
        }
        $file = file_get_contents($this->fullpath_to_test_no_filename  . DIRECTORY_SEPARATOR .  self::ROOT_TOKEN_FOLDER . DIRECTORY_SEPARATOR . $this->test_filename . '.token', $use_include_path = TRUE);

        if(!$file) {
            return array('errors' => 1, 'message' => "Could not read file");
        }

        try {
            $file = $this->ymal_parser->parse($file, $exceptionOnInvalidType = TRUE, $objectSupport = false);
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => $e->getMessage());
        }
        return $file;
    }


    public function setTokenContent($token = array()) {
        $this->token_content = $token;
    }

    public function delete() {
        $this->filesystem->remove($this->fullpath_to_test_no_filename  . DIRECTORY_SEPARATOR .  self::ROOT_TOKEN_FOLDER . DIRECTORY_SEPARATOR . $this->test_filename . '.token');
    }

    public function index() {
        return "Got All";
    }

    public function getAllTokenParents() {
        $file = $this->retrieve();
        return array_keys($file);
    }

    public function store() {
        if(is_array($this->token_content)) {
            $this->convertArrayToTokenFile();
        }
        $path_with_token_filename = $this->fullpath_to_test_no_filename . DIRECTORY_SEPARATOR . self::ROOT_TOKEN_FOLDER . DIRECTORY_SEPARATOR . $this->token_filename;
        $this->filesystem->dumpFile($path_with_token_filename, $this->token_string);
    }

    public function getRootTokenFolder() {
        return self::ROOT_TOKEN_FOLDER;
    }

    public function checkRoot() {
        return $this->filesystem->exists($this->fullpath_to_test_no_filename  . DIRECTORY_SEPARATOR .  self::ROOT_TOKEN_FOLDER);
    }

    public function checkForFile() {
        return $this->filesystem->exists($this->fullpath_to_test_no_filename  . DIRECTORY_SEPARATOR .  self::ROOT_TOKEN_FOLDER . DIRECTORY_SEPARATOR . $this->test_filename . '.token');
    }

    public function setRoot(){
        $dir = $this->fullpath_to_test_no_filename  . DIRECTORY_SEPARATOR .  self::ROOT_TOKEN_FOLDER;
        $this->filesystem->mkdir($dir);
    }

    public function convertArrayToTokenFile() {
        $this->token_string = $this->ymal_parser->dump($this->token_content, $inline = 2, $indent = 4, $exceptionOnInvalidType = true);
    }

    //@TODO this may not be needed anymore
    //  but I would like to consider a good
    //  steps to verify
    public function checkArrayFormat() {
        // if(isset($this->token_content[0][0])) {
        //     throw new \Exception('Seems your path is too many levels.');
        // }
    }
}
