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
        //1. check the root folder exists if not make it
        $check = $this->checkRoot();
        if(!$check) {
            try {
                $this->setRoot();
            } catch (\Exception $e) {
                return array('errors' => 1, 'message' => $e->getMessage());
            }
        }
        //2. Make sure the file does not exist alread
        $check_for_file = $this->checkForFile();
        if($check_for_file) {
            return array('errors' => 1, 'message' => "File exists already");
        }

        //3. Make sure array has default key
        try {
            $this->checkArrayFormat();
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => $e->getMessage());
        }

        //3. Create the yml file from the passed in array of content
        $yml = $this->convertArrayToTokenFile();

        //4. Write the array of the content to a yml file
        try {
            $this->store();
        } catch (\Exception $e) {
            return array('errors' => 1, 'message' => "could not write to file {$e->getMessage()}");
        }


        return array('errors' => 0, 'message' => "File saved");
    }

    public function update() {
        return "Updated";
    }

    public function retrieve() {
        return "Retrieve";
    }

    public function edit() {
        return "Editing";
    }

    public function delete() {
        return "Deleted";
    }

    public function index() {
        return "Got All";
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
    protected function validateFormat() {
        return "Good Yml";
    }

    protected function validateKeyValues() {
        return "All Keys have Values";
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

    public function checkArrayFormat() {
        if(!array_key_exists('default', $this->token_content)) {
            throw new \Exception('Default configuration is missing, please start your file yml file with default: then a new line for your default tokens');
        }
    }

}
