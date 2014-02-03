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
    protected $yaml;


    protected function instantiateClass($filename, $path, $content, $filesystem) {
        $this->yaml = new Yaml();
        return new TokenizerModel($filename, $path, $content, $filesystem, $this->yaml);
    }

    public function setUp()
    {
        //@TODO until I can get the test "test_create_pass_create_token_file_vfs"
        //  to work with the FileSystem class I still needed it for one test
        $files = new Filesystem();
        $this->root = vfsStream::setup('testDir');
        $this->token_content = array('foo' => 'bar', 'boo' => 'hoo');
        $this->token_content_bad = array('test', 'test', 'test');
        if(is_dir('/tmp/temptest')) {
            $files->remove('/tmp/temptest');
        }
    }

    public function test_create_fail_dir_creation()
    {
        $folder = vfsStream::newDirectory('testFolder_fail', 0000)
            ->at($this->root);
        $tokenizer = $this->instantiateClass('test_filename', vfsStream::url('testDir/testFolder_fail'), $this->token_content, new Filesystem());
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
        $tokenizer = $this->instantiateClass('test_filename_fail', vfsStream::url('testDir'), $this->token_content, new Filesystem());
        $result = $tokenizer->create();
        $this->assertEquals($result['errors'], 1);
        $this->assertEquals($result['message'], 'File exists already');
    }

    public function test_create_pass_create_token_folder()
    {
        $tokenizer = $this->instantiateClass('test_filename', vfsStream::url('testDir'), $this->token_content, new Filesystem());
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild($tokenizer->getRootTokenFolder()));
        $this->assertFalse($tokenizer->checkRoot());
        $result = $tokenizer->setRoot();
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild($tokenizer->getRootTokenFolder()));
    }

    public function test_create_pass_create_token_file()
    {
        //@TODO get this to work with VFS
        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem());
        $this->assertFileNotExists('/tmp/temptest/tokens/test_filename.token');
        $tokenizer->store();
        $this->assertFileExists('/tmp/temptest/tokens/test_filename.token');
        $should_be = $this->yaml->dump($this->token_content);
        $token_saved = file_get_contents('/tmp/temptest/tokens/test_filename.token');
        $this->assertSame($should_be, $token_saved);
    }

    //@TODO clear this up so there is new validations on this
    //  though the yml library may help enough
    //  I could check that there is only one level of tokens etc
//    public function test_create_poorly_formatted_array_to_yml() {
//        //@TODO get this to work with VFS
//        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest', $this->token_content_bad, new Filesystem());
//        $result = $tokenizer->create();
//        $this->assertEquals($result['errors'], 1);
//        $this->assertEquals($result['message'], 'Default configuration is missing, please start your file yml file with default: then a new line for your default tokens');
//    }


    public function test_create_pass_token_read_fail()
    {

        $tokenizer = $this->instantiateClass('test_filename', vfsStream::url('testDir/'), $this->token_content, new Filesystem());
        $result = $tokenizer->retrieve();
        $this->assertEquals($result['errors'], 1);
        $this->assertEquals($result['message'], 'File is missing please create one');
    }

    public function test_create_pass_token_read_pass()
    {
        //@TODO get this to work with VFS

        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem());
        $tokenizer->store();
        $result = $tokenizer->retrieve();
        $this->assertArrayHasKey('foo', $result['content']);
    }

    public function test_create_pass_token_update_pass()
    {
        //@TODO get this to work with VFS

        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem());
        $tokenizer->store();
        $result = $tokenizer->retrieve();
        $result['default']['foo2'] = 'bar2';
        $tokenizer->setTokenContent($result);
        $tokenizer->update();
        $result = $tokenizer->retrieve();
    }

    //Test bad update format
    public function test_delete_file() {

        $tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem());
        $tokenizer->store();
        $this->assertFileExists('/tmp/temptest/tokens/test_filename.token');
        $tokenizer->delete();
        $this->assertFileNotExists('/tmp/temptest/tokens/test_filename.token');
    }

    public function test_get_all_parent_tokens() {
        $tokenizer =$tokenizer = $this->instantiateClass('test_filename', '/tmp/temptest/', $this->token_content, new Filesystem());
        $tokenizer->store();
        $this->assertFileExists('/tmp/temptest/tokens/test_filename.token');
        $result = $tokenizer->getAllTokenParents();
        $this->assertEquals($result[1], 'boo');
    }
}
 
