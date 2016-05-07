<?php
namespace Gvre\Shortcodes;

include __DIR__ . '/../src/Manager.php';

/**
 * @todo Add more tests
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    private $sm;

    protected function setUp()
    {
        $this->sm = new Manager;
    }

    protected function tearDown()
    {
        unset($this->sm);
    }

    private function removeAll()
    {
        $this->sm->removeAll();
    }

    public function testAll()
    {
        $this->sm->add('shortcode', function() {
            return 'shortcode';
        });
        $this->assertTrue($this->sm->exists('shortcode'));
        $this->assertEquals($this->sm->execute('[shortcode]'), 'shortcode');

        $this->sm->remove('not_exists');

        $this->sm->remove('shortcode');
        $this->assertFalse($this->sm->exists('shortcode'));

        $str = 'test [shortcode name="foo" id="bar"] test';
        $res = $this->sm->execute($str);
        $this->assertEquals($this->sm->execute($str), $str);
        $this->sm->add('shortcode', function($args) {
            return sprintf('<shortcode name="%s" id="%s">', $args['name'], $args['id']);
        });
        $this->assertEquals($this->sm->execute($str), 'test <shortcode name="foo" id="bar"> test');

        // shortcode with closing tag
        $str = 'test [h1 class="heading"]heading text[/h1] test';
        $this->sm->add('h1', function($args) {
            return sprintf('<h1 class="%s">%s</h1>', $args['class'], $args['content']);
        });
        $this->assertEquals($this->sm->execute($str), 'test <h1 class="heading">heading text</h1> test');

        $str = 'test count [code lang="php"]echo "hello world";[/code]';
        $this->sm->add('code', [ '\Gvre\Shortcodes\Foo', 'bar' ]);
        $this->assertEquals($this->sm->execute($str), 'test count 2'); // lang and content

        $this->sm->removeAll();
    }
}

class Foo
{
    public function bar($args): int
    {
        return count($args);
    }
}
