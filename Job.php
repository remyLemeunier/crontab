<?php

namespace Yzalis\Components\Crontab;

/**
 * Represent a cron job
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
    private $hour = "10";
    
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
     * @var $hash
     */
    private $hash = null;

    /**
     * Parse crontab line into Job object
     *
     * @param string $jobSpec
     * @return Yzalis\Components\
     */
    public function parse($jobSpec)
    {
        if ("#" == substr($jobSpec, 0, 1)) {
            $this->setActive(false);
            $jobSpec = trim(substr($jobSpec, 1));
        }

        $detail = explode(' ', $jobSpec, 6); // var_dump($detail);
        
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
        ) = $detail; // var_dump($command);

        $comments = null;
        if ($pos = strpos($command, '#')) { // var_dump($command);
            $comments = trim(substr($command, $pos + 1)); // var_dump($comments);
            $command = trim(substr($command, 0, $pos)); // var_dump($command);
        }

        $this
            ->setMinute($minute)
            ->setHour($hour)
            ->setDayOfMonth($dayOfMonth)
            ->setMonth($month)
            ->setDayOfWeek($dayOfWeek)
            ->setCommand($command)
            ->setComments($comments)
        ;

        return $this->generateHash();
    }

    private function generateHash()
    {
        $this->hash = hash('md5', $this->render());

        return $this;
    }

    /**
     * Render the job for crontab
     * 
     * @return string
     */
    public function render()
    {
        $entry = array(
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMonth(),
            $this->getMonth(),
            $this->getDayOfWeek(),
            $this->getCommand(),
        );

        $line = "";
        if ($this->getComments()) {
            $line .= $this->prepareComments();
        }

        $line .= ($this->getActive()) ? "#": "";
        $line .= implode(" ", $entry) . "\n";

        return $line;
    }

    public function prepareComments()
    {
        return '# ' . $this->getComments();
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
     * @return string
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->generateHash();
        }

        return $this->hash;
    }

    /**
     * Set the minute (* 1 1-10,11-20,30-59 1-59 *\/1)
     *
     * @param string
     * 
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * Set the comments
     *
     * @param string
     * 
     * @return $this
     */
    public function setComments($comments)
    {
        if (is_array($comments))
        {
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
     * @return $this
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
