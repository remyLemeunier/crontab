<?php

use Yzalis\Components\Crontab\Crontab;
use Yzalis\Components\Crontab\Job;

/**
 * CSSToInlineStylesTest
 */
class CrontabTest extends \PHPUnit_Framework_TestCase
{
    private $crontab;

    private $job1;

    private $job2;

    public function setUp()
    {
        $this->crontab = new Crontab();
        
        $this->job1 = new Job();

        $this->job2 = new Job();
    }

    public function testComments()
    {
        $this->job1->setComments('comment');
        $this->assertStringStartsWith('# comment', $this->job1->prepareComments());

        $this->job1->setComments(array('comment l1', 'comment l2'));
        $this->assertStringStartsWith('# comment l1 comment l2', $this->job1->prepareComments());
    }

    public function testAddingJob()
    {
        $this->assertCount(0, $this->crontab->getJobs());

        $job = new Job();
        $this->crontab->addJob($job);
        $this->assertCount(1, $this->crontab->getJobs());

        $job = new Job();
        $this->crontab->addJob($job);
        $this->assertCount(2, $this->crontab->getJobs());
    }

    public function testRender()
    {
        $this->crontab->addJob($this->job1)->addJob($this->job2->setActive(false));
        //var_dump($this->crontab->render());
    }

    public function testParseFile()
    {
        $filename = __DIR__ . '/Fixtures/valid_crontab.txt';
        $this->crontab->addJobsFromFile($filename);
        $this->assertCount(8, $this->crontab->getJobs());
    }

    public function testRemovingJobs()
    {
        $this->crontab->removeAllJobs();
        $this->assertCount(0, $this->crontab->getJobs());
    }
}