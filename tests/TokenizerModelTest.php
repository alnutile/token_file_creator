<?php
/**
 * @TODO
 * mock Filesystem and Yaml classes as well
 */
namespace Tokenizer;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class TokenizerModelTest extends \PHPUnit_Framework_TestCase {
    protected $root;
    protected $tmp;
    protected $token_content;
    protected $token_content_bad;


    protected function instantiateClass($filename, $path, $content, $filesystem, $yaml) {
        return new TokenizerModel($filename, $path, $content, $filesystem, $yaml);
    }

    public function setUp()
    {
        //@TODO until I can get the test "test_create_pass_create_token_file_vfs"
        //  to work with the FileSystem class I still needed it for one test
        $files = new Filesystem();
        $this->root = vfsStream::setup('testDir');
        $this->token_content = array(array('default' => array('foo' => 'bar', 'boo' => 'hoo')));
        $this->token_content_bad = array('test', 'test', 'test');
        if(is_dir('/tmp/temptest')) {
            $files->remove('/tmp/temptest');
        }
    }

    public function test_create_fail_dir_creation()
    {
        $folder = vfsStream::newDirectory('testFolder_fail', 0000)
            ->at($this->root);
        $tokenizer = $this->instantiateClass('test_filename', vfsStream::url('testDir/testFolder_fail'), $this->token_content, new Filesystem(), new Yaml());
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild($tokenizer->getRootTokenFolder()));
        $this->assertFalse($tokenizer->checkRoot());
        $result = $tokenizer->create();
        $this->assertEquals($result['errors'], 1);
        $this->assertEquals($result['message'], 'Failed to create "vfs://testDir/testFolder_fail/tokens".');
    }

    public function test_create_fail_file_exists()
    {
        $folder = vfsStream::newFile('tokens/test_filename_fail.token', 0777)
            ->at($this->root);
        $tokenizer = $this->instantiateClass('test_filename_fail', vfsStream::url('testDir'), $this->token_content, new Filesystem(), new Yaml());
        $result = $tokenizer->create();
        $this->assertEquals($result['errors'], 1);
        $this->assertEquals($result['message'], 'File exists already');
    }

    public function test_create_pass_create_token_folder()
    {
        $tokenizer = $this->instantiateClass('test_filename', vfsStream::url('testDir'), $this->token_content, new Filesystem(), new Yaml());
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild($tokenizer->getRootTokenFolder()));
        $this->assertFalse($tokenizer->checkRoot());
        $result = $tokenizer->setRoot();
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild($tokenizer->getRootTokenFolder()));
    }

    /**
     * @TODO get this to work with VFS
     */
//    public function test_create_pass_create_token_file_vfs()
//    {
//        $folder = vfsStream::newDirectory('testFolder_pass', 0777)
//            ->at($this->root);
//
//        $tokenizer = new TokenizerModel('test_filename', vfsStream::url('testDir/testFolder_pass'), $this->token_content, new Filesystem());
//        $tokenizer->setRoot();
//        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('testFolder_pass/' . $tokenizer->getRootTokenFolder()));
//        $result = $tokenizer->store();
//        $root_folder = $tokenizer->getRootTokenFolder();
//        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('testFolder_pass/' . $root_folder . '/test_filename.token'));
//    }

    public function test_create_pass_create_token_file()
    {
        //@TODO get this to work with VFS
        $yaml = new Yaml();
        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem(), $yaml);
        $this->assertFalse(file_exists('/tmp/temptest/tokens/test_filename.token'));
        $tokenizer->store();
        $this->assertTrue(file_exists('/tmp/temptest/tokens/test_filename.token'));
        $should_be = $yaml->dump($this->token_content);
        $token_saved = file_get_contents('/tmp/temptest/tokens/test_filename.token');
        $this->assertSame($should_be, $token_saved);
    }

    public function test_create_poorly_formatted_array_to_yml() {
        //@TODO get this to work with VFS
        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest', $this->token_content_bad, new Filesystem(), new Yaml());
        $result = $tokenizer->create();
        $this->assertEquals($result['errors'], 1);
        $this->assertEquals($result['message'], 'Default configuration is missing, please start your file yml file with default: then a new line for your default tokens');
    }

}
 