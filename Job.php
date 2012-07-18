<?php

namespace Yzalis\Components\Crontab;

/**
 * Represent a cron job
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class Job
{
    /**
     * @var $regex
     */
    private $regex = array(
        'minute'     => '/^((\*)|(\d?([-,\d?])*)|(\*\/\d?))$/',
        'hour'       => '/^((\*)|(\d?([-,\d?])*)|(\*\/\d?))$/',
        'dayOfMonth' => '/^((\*)|(\d?([-,\d?])*)|(\*\/\d?))$/',
        'month'      => '/^((\*)|(\d?([-,\d?])*)|(\*\/\d?))$/',
        'dayOfWeek'  => '/^((\*)|(\d?([-,\d?])*)|(\*\/\d?))$/',
        'command'    => '/^(.)*$/',
    );

    /**
     * @var string
     */
    private $minute = "0";

    /**
     * @var string
     */
    private $hour = "*";

    /**
     * @var string
     */
    private $dayOfMonth = "*";

    /**
     * @var string
     */
    private $month = "*";

    /**
     * @var string
     */
    private $dayOfWeek = "*";

    /**
     * @var string
     */
    private $command = null;

    /**
     * @var string
     */
    private $comments = null;

    /**
     * @var boolean
     */
    private $active = true;

    /**
     * @var $log
     */
    private $log = null;

    /**
     * @var $hash
     */
    private $hash = null;

    /**
     * Parse crontab line into Job object
     *
     * @param string $jobSpec
     *
     * @return Yzalis\Components\Crontab\Job
     */
    public function parse($jobSpec)
    {
        // If the line begins with a # it means it's a comment
        if ("#" == substr($jobSpec, 0, 1)) {
            $this->setActive(false);
            $jobSpec = trim(substr($jobSpec, 1));
        }

        // A line have always 6 arguments
        $detail = explode(' ', $jobSpec, 6);

        if (count($detail) != 6) {
            throw new \InvalidArgumentException('Wrong job number of arguments.');
        }

        list(
            $minute,
            $hour,
            $dayOfMonth,
            $month,
            $dayOfWeek,
            $command
        ) = $detail;

        $comments = $log = null;

        // Comments can be found after the command check if that is the case
        if ($pos = strpos($command, '#')) {
            $comments = trim(substr($command, $pos + 1));
            $command = trim(substr($command, 0, $pos));
        }

        $this
            ->setMinute($minute)
            ->setHour($hour)
            ->setDayOfMonth($dayOfMonth)
            ->setMonth($month)
            ->setDayOfWeek($dayOfWeek)
            ->setCommand($command)
            ->setLog($log)
            ->setComments($comments)
        ;

        return $this->generateHash();
    }

    /**
     * Generate a unique hash related to the job entries
     *
     * @return Yzalis\Components\Crontab\Job
     */
    private function generateHash()
    {
        $this->hash = hash('md5', serialize(array(
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMonth(),
            $this->getMonth(),
            $this->getDayOfWeek(),
            $this->getCommand(),

        )));

        return $this;
    }

    /**
     * Get an array of job entries
     *
     * @return array
     */
    public function getEntries()
    {
        return array(
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMonth(),
            $this->getMonth(),
            $this->getDayOfWeek(),
            $this->getCommand(),
            $this->prepareLog(),
            $this->prepareComments(),
        );
    }

    /**
     * Render the job for crontab
     *
     * @return string
     */
    public function render()
    {
        if (null === $this->getCommand()) {
            throw new \InvalidArgumentException('You must specify a command to run.');
        }

        // Create / Recreate a line in the crontab
        $line = $this->getActive() ? "": "#";
        $line .= implode(" ", $this->getEntries());

        return $line . "\n";
    }

    /**
     * Prepare comments
     *
     * @return string or null
     */
    public function prepareComments()
    {
        if (null !== $this->getComments()) {
            return '# ' . $this->getComments();
        } else {
            return null;
        }
    }

    /**
     * Prepare log
     *
     * @return string or null
     */
    public function prepareLog()
    {
        if (null !== $this->getLog()) {
            return '> ' . $this->getLog();
        } else {
            return null;
        }
    }

    /**
     * Return the minute
     *
     * @return string
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * Return the hour
     *
     * @return string
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Return the day of month
     *
     * @return string
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    /**
     * Return the month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Return the day of week
     *
     * @return string
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * Return the command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Return the comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Return the active status
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Return the job unique hash
     *
     * @return Job
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->generateHash();
        }

        return $this->hash;
    }

    /**
     * Return log path
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set the minute (* 1 1-10,11-20,30-59 1-59 *\/1)
     *
     * @param string
     *
     * @return Job
     */
    public function setMinute($minute)
    {
        if (!preg_match($this->regex['minute'], $minute)) {
            throw new \InvalidArgumentException(sprintf('Minute "%s" is incorect', $minute));
        }

        $this->minute = $minute;

        return $this->generateHash();
    }

    /**
     * Set the hour
     *
     * @param string
     *
     * @return Job
     */
    public function setHour($hour)
    {
        if (!preg_match($this->regex['hour'], $hour)) {
            throw new \InvalidArgumentException(sprintf('Hour "%s" is incorect', $hour));
        }

        $this->hour = $hour;

        return $this->generateHash();
    }

    /**
     * Set the day of month
     *
     * @param string
     *
     * @return Job
     */
    public function setDayOfMonth($dayOfMonth)
    {
        if (!preg_match($this->regex['dayOfMonth'], $dayOfMonth)) {
            throw new \InvalidArgumentException(sprintf('DayOfMonth "%s" is incorect', $dayOfMonth));
        }

        $this->dayOfMonth = $dayOfMonth;

        return $this->generateHash();
    }

    /**
     * Set the month
     *
     * @param string
     *
     * @return Job
     */
    public function setMonth($month)
    {
        if (!preg_match($this->regex['month'], $month)) {
            throw new \InvalidArgumentException(sprintf('Month "%s" is incorect', $month));
        }

        $this->month = $month;

        return $this->generateHash();
    }

    /**
     * Set the day of week
     *
     * @param string
     *
     * @return Job
     */
    public function setDayOfWeek($dayOfWeek)
    {
        if (!preg_match($this->regex['dayOfWeek'], $dayOfWeek)) {
            throw new \InvalidArgumentException(sprintf('DayOfWeek "%s" is incorect', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;

        return $this->generateHash();
    }

    /**
     * Set the command
     *
     * @param string
     *
     * @return Job
     */
    public function setCommand($command)
    {
        if (!preg_match($this->regex['command'], $command)) {
            throw new \InvalidArgumentException(sprintf('Command "%s" is incorect', $command));
        }

        $this->command = $command;

        return $this->generateHash();
    }

    /**
     * Set the log file path
     *
     * @param string
     *
     * @return Job
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this->generateHash();
    }

    /**
     * Set the comments
     *
     * @param string
     *
     * @return Job
     */
    public function setComments($comments)
    {
        if (is_array($comments)) {
            $comments = implode($comments, ' ');
        }

        $this->comments = $comments;

        return $this->generateHash();
    }

    /**
     * Set the active status
     *
     * @param boolean
     *
     * @return Job
     */
    public function setActive($active)
    {
        if (!is_bool($active)) {
            throw new \InvalidArgumentException("setActive must receive a boolean");
        }

        $this->active = $active;

        return $this->generateHash();
    }
}
