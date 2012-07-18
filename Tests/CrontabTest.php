<?php

use Yzalis\Components\Crontab\Crontab;
use Yzalis\Components\Crontab\Job;

/**
 * CrontabTest
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
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
        $this->job1->setCommand('cmd');

        $this->job2 = new Job();
        $this->job2->setCommand('cmd2');
        $this->job2->setActive(false);
    }

    public function testSetterGetter()
    {
        $this->assertNull($this->crontab->getUser());
        $this->assertEquals('root', $this->crontab->setUser('root')->getUser());

        $this->assertCount(0, $this->crontab->getJobs());
        $this->crontab->setJobs(array($this->job1, $this->job2));
        $this->assertCount(2, $this->crontab->getJobs());

        $this->assertEquals("", $this->crontab->getMailto());
        $this->assertEquals("contact@yzalis.com", $this->crontab->setMailto('contact@yzalis.com')->getMailto());
        $this->assertStringStartsWith("MAILTO=contact@yzalis.com"."\n", $this->crontab->render());

        $this->crontab->removeAllJobs();
        $this->assertCount(0, $this->crontab->getJobs());

        $job = new Job();
        $this->crontab->addJob($job);
        $this->assertCount(1, $this->crontab->getJobs());
        $this->crontab->addJob($job);
        $this->assertCount(1, $this->crontab->getJobs());

        $job = new Job();
        $job->setCommand('test');
        $this->crontab->addJob($job);
        $this->assertCount(2, $this->crontab->getJobs());

        $this->crontab->removeAllJobs();
        $this->crontab->setJobs(array($this->job1, $this->job2));
        $this->crontab->removeJob($this->job1);
        $this->assertCount(1, $this->crontab->getJobs());
        $job = $this->crontab->getJobs();
        $this->assertEquals(array_shift($job), $this->job2);
    }

    public function testRender()
    {
        $this->crontab
            ->addJob($this->job1)
            ->addJob($this->job2)
        ;
        $this->assertEquals(
            "0 * * * * cmd  " . "\n" . "#0 * * * * cmd2  " . "\n",
            $this->crontab->render()
        );
    }

    public function testWriteFile()
    {
        $this->crontab->addJob($this->job1);
        $this->assertEquals('Yzalis\Components\Crontab\Crontab', get_class($this->crontab->write()));
        $this->crontab->removeAllJobs();
        $this->assertEquals('Yzalis\Components\Crontab\Crontab', get_class($this->crontab->write()));
    }

    public function testParseFile()
    {
        $filename = __DIR__ . '/Fixtures/crontab1.txt';
        $this->crontab->addJobsFromFile($filename);
        $this->assertCount(8, $this->crontab->getJobs());

        $filename = __DIR__ . '/Fixtures/crontab1.txt';
        $jobs = $this->crontab->parseFile($filename);
        $this->assertCount(8, $jobs);
    }
}
