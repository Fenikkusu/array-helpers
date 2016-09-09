<?php
    /**
     * Created by PhpStorm.
     * User: fenikkusu
     * Date: 9/8/16
     * Time: 10:41 PM
     */
    namespace TwistersFury\Helpers\Arrays\tests;

    use TwistersFury\Helpers\Arrays\ArrayWrapper;

    class ArrayWrapperTest extends \PHPUnit_Framework_TestCase {
        /** @var  \TwistersFury\Helpers\Arrays\ArrayWrapper */
        protected $testSubject;

        public function setUp() {
            $this->testSubject = new ArrayWrapper(
                [
                    'something' => 'else',
                    'child' => [
                        'something' => FALSE
                    ],
                    'yet_another' => TRUE
                ]
            );
        }

        public function testMergeConfig() {
            $this->assertFalse($this->testSubject->hasProperty('other'));
            $this->assertSame($this->testSubject, $this->testSubject->mergeConfig(['something' => 'other', 'other' => 'something']));
            $this->assertTrue($this->testSubject->hasProperty('other'));
            $this->assertEquals('other', $this->testSubject->getProperty('something'));
        }

        /**
         * @covers \TwistersFury\Helpers\Arrays\ArrayWrapper::getProperty()
         * @covers \TwistersFury\Helpers\Arrays\ArrayWrapper::hasProperty()
         */
        public function testGetProperty() {
            $this->assertInstanceOf('\TwistersFury\Helpers\Arrays\ArrayWrapper', $this->testSubject->getProperty('child'), 'Failed Getting Child Wrapper');
            $this->assertEquals('else', $this->testSubject->getProperty('something'), 'Failed Getting Property');
            $this->assertFalse($this->testSubject->getProperty('child')->getProperty('something'));
            $this->assertNull($this->testSubject->getProperty('nothing'), 'Failed Getting Default');
            $this->assertFalse($this->testSubject->getProperty('nothing', FALSE), 'Failed Getting Specified Default');
        }

        public function testSetProperty() {
            $this->assertEquals(NULL, $this->testSubject->getProperty('property'));
            $this->assertSame($this->testSubject, $this->testSubject->setProperty('property', 'value'));
            $this->assertEquals('value', $this->testSubject->getProperty('property'));
        }

        public function testRemoveProperty() {
            $this->assertEquals('else', $this->testSubject->getProperty('something'));
            $this->assertSame($this->testSubject, $this->testSubject->removeProperty('something'));
            $this->assertEquals(NULL, $this->testSubject->getProperty('something'));
        }

        public function testArrayAccess() {
            $this->assertEquals('else', $this->testSubject['something']);

            $this->testSubject['something'] = 'other';
            $this->assertEquals('other', $this->testSubject['something']);

            unset($this->testSubject['something']);
            $this->assertFalse(isset($this->testSubject['something']));
        }

        /**
         * @covers \TwistersFury\Helpers\Arrays\ArrayWrapper::__call()
         */
        public function testMagicMethod() {
            $this->assertEquals('else', $this->testSubject->getSomething());
            $this->assertSame($this->testSubject, $this->testSubject->setSomething('other'));
            $this->assertEquals('other', $this->testSubject->getSomething());
            $this->assertSame(FALSE, $this->testSubject->getNothing(FALSE));
            $this->assertFalse($this->testSubject->getChild()->isOther());
            $this->assertFalse($this->testSubject->getChild()->canOther());
            $this->assertFalse($this->testSubject->hasAnother());
            $this->assertTrue($this->testSubject->getYetAnother());
        }

        /**
         * @expectedException \RuntimeException
         */
        public function testMagicMethodThrowsException() {
            $this->testSubject->thisSucks();
        }

        /**
         * @dataProvider dpPrepareKey
         */
        public function testPrepareKey($testKey, $compareKey) {
            $reflectionMethod = new \ReflectionMethod('\TwistersFury\Helpers\Arrays\ArrayWrapper', 'prepareKey');
            $reflectionMethod->setAccessible(TRUE);

            $this->assertEquals($compareKey, $reflectionMethod->invoke($this->testSubject, $testKey));
        }

        public function dpPrepareKey() {
            return [
                ['something'    , 'something'],
                ['somethingElse', 'something_else'],
                ['YetAnother'   , 'yet_another'],
                ['AndYetAnother', 'and_yet_another']
            ];
        }
    }
