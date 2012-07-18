<?php

namespace Yzalis\Components\Crontab;

use Yzalis\Components\Crontab\Job;

/**
 * Represent a crontab
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class Crontab
{
    /**
     * A collection of job
     *
     * @var array $jobs  Yzalis\Compoenents\Crontab\Job
     */
    private $jobs = array();

    /**
     * Location of the crontab executable
     *
     * @var string
     */
    public $crontabExecutable = '/usr/bin/crontab';

    /**
     * Location to save the temporary crontab file.
     *
     * @var string
     */
    private $tempFile = null;

    /**
     * The user to
     *
     * @var $user
     */
    private $user = null;

    /**
     * An email where crontab execution report will be sent
     *
     * @var $user
     */
    private $mailto = "";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->generateTempFile();
    }

    /**
     * Destrutor
     */
    public function __destruct()
    {
        if ($this->tempFile && is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    /**
     * Render the crontab and associated jobs
     *
     * @return string
     */
    public function render()
    {
        $content = "";
        if ($this->getMailto()) {
            $content = "MAILTO=" . $this->getMailto() . "\n";
        }
        foreach ($this->getJobs() as $job) {
            $content .= $job->render();
        }

        return $content;
    }

    /**
     * Parse input cron file to cron entires and add them to the current object
     *
     * @param string $filename
     *
     * @return Crontab
     */
    public function addJobsFromFile($filename)
    {
        // parse file and retrieve valid jobs
        $newJobs = $this->parseFile($filename);

        // add new jobs to the current crontab
        if (count($this->getJobs()) == 0) {
            $this->setJobs($newJobs);
        } else {
            foreach ($newJobs as $newJob) {
                foreach ($this->getJobs() as $job) {
                    if ($newJob->getHash() !== $job->getHash()) {
                        $this->addJob($newJob);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Parse input cron file to cron entires
     *
     * @param string $filename
     */
    public function parseFile($filename)
    {
        // check the availability of the file
        $path = realpath($filename);
        if (!$path || !is_readable($path)) {
            throw new \InvalidArgumentException(sprintf('"%s" don\'t exists or isn\'t readable', $filename));
        }

        // parse every line of the file
        $lines = file($path);
        foreach ($lines as $lineno => $line) {
            try {
                $job = new Job();
                $job->parse($line);
                $newJobs[] = $job;
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Line #%d of file: "%s" is invalid. %s', $lineno, $path, $e));
            }
        }

        return $newJobs;
    }

    /**
     * Write the crontab to the system
     *
     * @param
     *
     * @return
     */
    public function write()
    {
        $this->writeCrontabInTempFile();

        $out = $this->exec($this->crontabCommand() . ' ' . $this->tempFile . ' 2>&1', $ret);
        if ($ret != 0) {
            throw new \UnexpectedValueException(
                $out . "\n"  . $this->render(), $ret
            );
        }

        return $this;
    }

    private function writeCrontabInTempFile()
    {
        file_put_contents($this->tempFile, $this->render(), LOCK_EX);
    }

    /**
     * Calcuates crontab command
     *
     * @return string
     */
    protected function crontabCommand()
    {
        $cmd = '';
        if ($this->getUser()) {
            $cmd .= sprintf('sudo -u %s ', $this->getUser());
        }
        $cmd .= $this->getCrontabExecutable();

        return $cmd;
    }

    /**
     * Runs command in terminal
     *
     * @param string  $command
     * @param integer $returnVal
     *
     * @return string
     */
    private function exec($command, & $returnVal)
    {
        ob_start();
        system($command, $returnVal);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Generate temporary crontab file
     *
     * @return Crontab
     */
    protected function generateTempFile()
    {
        if ($this->tempFile && is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
        $tempDir = sys_get_temp_dir();
        $this->tempFile = tempnam($tempDir, 'crontemp');
        chmod($this->tempFile, 0666);

        return $this;
    }

    /**
     * Get unix user to add crontab
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set unix user to add crontab
     *
     * @param string $user
     *
     * @return Crontab
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get crontab executable location
     *
     * @return string
     */
    public function getCrontabExecutable()
    {
        return $this->crontabExecutable;
    }

    /**
     * Set unix user to add crontab
     *
     * @param string $crontabExecutable
     *
     * @return Crontab
     */
    public function setCrontabExecutable($crontabExecutable)
    {
        $this->crontabExecutable = $crontabExecutable;

        return $this;
    }

    /**
     * Get mailto
     *
     * @return string
     */
    public function getMailto()
    {
        return $this->mailto;
    }

    /**
     * Set mailto
     *
     * @param string $mailto
     *
     * @return Crontab
     */
    public function setMailto($mailto)
    {
        if (!filter_var($mailto, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Mailto "%s" is incorect', $mailto));
        }

        $this->mailto = $mailto;

        return $this;
    }

    /**
     * Get all crontab jobs
     *
     * @return array An array of Yzalis\Components\Job
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Add a new job to the crontab
     *
     * @param Yzalis\Components\Job $job
     *
     * @return Crontab
     */
    public function addJob(Job $job)
    {
        $this->jobs[$job->getHash()] = $job;

        return $this;
    }

    /**
     * Adda new job to the crontab
     *
     * @param array $jobs
     *
     * @return Crontab
     */
    public function setJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->addJob($job);
        }

        return $this;
    }

    /**
     * Remove all job for current crontab
     *
     * @return Crontab
     */
    public function removeAllJobs()
    {
        $this->jobs = array();

        return $this;
    }

    /**
     * Remove all job for current crontab
     *
     * @return Crontab
     */
    public function removeJob($job)
    {
        unset($this->jobs[$job->getHash()]);

        return $this;
    }
}
