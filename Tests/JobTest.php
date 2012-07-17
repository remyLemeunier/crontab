<?php

use Yzalis\Components\Crontab\Crontab;
use Yzalis\Components\Crontab\Job;

/**
 * JobTest
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    private $job;

    public function setUp()
    {
        $this->job = new Job();
    }

    public function testSetterGetter()
    {
    	$this->assertEquals('0', $this->job->getMinute());
    	$this->assertEquals('*', $this->job->setMinute('*')->getMinute());
    	$this->assertEquals('*/2', $this->job->setMinute('*/2')->getMinute());
    	$this->assertEquals('0-59', $this->job->setMinute('0-59')->getMinute());
    	$this->assertEquals('0,59', $this->job->setMinute('0,59')->getMinute());
    	$this->assertEquals('0,50-58', $this->job->setMinute('0,50-58')->getMinute());

    	$this->assertEquals('*', $this->job->getHour());
    	$this->assertEquals('*', $this->job->setHour('*')->getHour());
    	$this->assertEquals('*/2', $this->job->setHour('*/2')->getHour());
    	$this->assertEquals('0-23', $this->job->setHour('0-23')->getHour());
    	$this->assertEquals('0,23', $this->job->setHour('0,23')->getHour());
    	$this->assertEquals('0,20-23', $this->job->setHour('0,20-23')->getHour());

    	$this->assertEquals('*', $this->job->getDayOfMonth());
    	$this->assertEquals('*', $this->job->setDayOfMonth('*')->getDayOfMonth());
    	$this->assertEquals('*/2', $this->job->setDayOfMonth('*/2')->getDayOfMonth());
    	$this->assertEquals('1-31', $this->job->setDayOfMonth('1-31')->getDayOfMonth());
    	$this->assertEquals('1,31', $this->job->setDayOfMonth('1,31')->getDayOfMonth());
    	$this->assertEquals('1,20-31', $this->job->setDayOfMonth('1,20-31')->getDayOfMonth());

    	$this->assertEquals('*', $this->job->getMonth());
    	$this->assertEquals('*', $this->job->setMonth('*')->getMonth());
    	$this->assertEquals('*/2', $this->job->setMonth('*/2')->getMonth());
    	$this->assertEquals('1-12', $this->job->setMonth('1-12')->getMonth());
    	$this->assertEquals('1,12', $this->job->setMonth('1,12')->getMonth());
    	$this->assertEquals('1,10-12', $this->job->setMonth('1,10-12')->getMonth());

    	$this->assertEquals('*', $this->job->getDayOfWeek());
    	$this->assertEquals('*', $this->job->setDayOfWeek('*')->getDayOfWeek());
    	$this->assertEquals('*/2', $this->job->setDayOfWeek('*/2')->getDayOfWeek());
    	$this->assertEquals('0-7', $this->job->setDayOfWeek('0-7')->getDayOfWeek());
    	$this->assertEquals('0,7', $this->job->setDayOfWeek('0,7')->getDayOfWeek());
    	$this->assertEquals('0,4-7', $this->job->setDayOfWeek('0,4-7')->getDayOfWeek());

    	$this->assertNull($this->job->getComments());
    	$this->assertEquals('comment', $this->job->setComments('comment')->getComments());
        $this->assertEquals('# comment', $this->job->prepareComments());
        $this->assertEquals('# comment l1 comment l2', $this->job->setComments(array('comment l1', 'comment l2'))->prepareComments());

        $this->assertNull($this->job->getCommand());
        $this->assertEquals('myAmazingCommandToRun', $this->job->setCommand('myAmazingCommandToRun')->getCommand());

        $this->assertFalse($this->job->setActive(false)->getActive());
        $this->assertStringStartsWith("#0,50", $this->job->render());
        $this->assertTrue($this->job->setActive(true)->getActive());
        $this->assertStringStartsWith("0,50", $this->job->render());

        $this->assertNull($this->job->getLog());
        $this->assertEquals('/cron_log', $this->job->setLog('/cron_log')->getLog());
        $this->assertEquals('> /cron_log', $this->job->prepareLog());

        $this->assertEquals(
        	array(
        		'0,50-58',
        		'0,20-23',
        		'1,20-31',
        		'1,10-12',
        		'0,4-7',
        		'myAmazingCommandToRun',
        		'> /cron_log',
        		'# comment l1 comment l2'
        	),
        	$this->job->getEntries()
        );
        
        $this->assertEquals(
    		'0,50-58 0,20-23 1,20-31 1,10-12 0,4-7 myAmazingCommandToRun > /cron_log # comment l1 comment l2'."\n",
    		$this->job->render()
    	);
    }


    /**
     * @expectedException InvalidArgumentException 
     */
    public function testRenderCommandException()
    {
    	$this->job->render();
    }
}