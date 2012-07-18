Crontab Component
=================

[![Build Status](https://secure.travis-ci.org/yzalis/crontab.png?branch=master)](http://travis-ci.org/yzalis/crontab)

Crontab provide a php 5.3 lib to create crontab file.

	use Yzalis\Components\Crontab\Crontab;
	use Yzalis\Components\Crontab\Job;

	$job = new Job();
	$job
		->setMinute('*/5')
		->setHour('*')
		->setDayOfMonth('*')
		->setMonth('1,6')
		->setDayOfWeek('*')
		->setCommand('myAmazingCommandToRunPeriodically')
	;

	$crontab = new Crontab();
	$crontab->setMailto('your.email@email.com');
	$crontab->addJob($job);

	$crontab->write();

You can render what you have created:

	echo $crontab->render();

You can also parse existing crontab file

	use Yzalis\Components\Crontab\Crontab;
	use Yzalis\Components\Crontab\Job;

    $crontab = new Crontab();
    $jobs = $crontab->parseFile($filename);

And then you can delete a job you don't want anymore:
	$crontab->removeJob($theJobYouWantToDelete);

Resources
---------

You can run the unit tests with the following command. You need to be in the crontab directory and have phpunit installed on your computer:

    phpunit

Unit tests ll delete your actual crontab. Save it before testing the crontab component.