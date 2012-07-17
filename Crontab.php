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
     * @param string $path
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
        $content = $this->render();

        echo '@toto : $crontab->write()';exit;

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