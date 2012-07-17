<?php

namespace Yzalis\Components\Crontab;

use Yzalis\Components\Crontab\Job;

/**
 * Represent a crontab
 */
class Crontab
{
    /**
     * A collection of Job
     *
     * @var array $jobs A collection of Yzalis\Compoenents\Crontab\Job
     */
    private $jobs = array();

    /**
     * Render the crontab and associated jobs
     * 
     * @return string
     */
    public function render()
    {
        $content = "";
        foreach ($this->getJobs() as $job) {
            $content .= $job->render();
        }

        return $content;
    }

    /**
     * Parse input cron file to cron entires and add them to the current object
     *
     * @param string $filebame
     * 
     * @return $this
     */
    public function addJobsFromFile($filename)
    {
        $path = realpath($filename);
        if (!$path || !is_readable($path)) {
            throw new \InvalidArgumentException(sprintf('"%s" don\'t exists or isn\'t readable', $filename));
        }

        $this->parseFile($path);

        return $this;
    }


    /**
     * Parse input cron file to cron entires
     *
     * @param string $path
     */
    private function parseFile($path)
    {
        $newJobs = $errors = array();

        $lines = file($path);
        foreach ($lines as $lineno => $line) {
            try {
                $job = new Job();
                $job->parse($line);
                $newJobs[] = $job;
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Line #%d of file: "%s" is invalid.', $lineno, $path));
            }
        }

        if (count($this->getJobs()) == 0) {
            $this->setJobs($newJobs);
        } else {
            foreach ($newJobs as $newJob) {
                foreach ($this->getJobs() as $job) {
                echo 'ici ';
                    if ($newJob->getHash() !== $job->getHash()) {
                        $this->addJob($newJob);
                    }
                }
            }
        }
    }

    public function write()
    {
        $content = $this->render();


    }

    /**
     * Prepare the command to write somme content in crontab file
     *
     * @param string $content
     * 
     * @return string
     */
    private function _prepareCommand($content)
    {
        return 'crontab';
    }

    /*
    private function _exec()
    {
        ob_start();
        system($command, $returnVal);
        $output = ob_get_clean();

        return $output;
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
     * Adda new job to the crontab
     *
     * @param Yzalis\Components\Job $job
     * 
     * @return $this
     */
    public function addJob(Job $job)
    {
        $this->jobs[] = $job;

        return $this;
    }

    /**
     * Adda new job to the crontab
     *
     * @param Yzalis\Components\Job $job
     * 
     * @return $this
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
     * @return $this
     */
    public function removeAllJobs()
    {
        $this->jobs = array();

        return $this;
    }
}