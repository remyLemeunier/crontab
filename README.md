Crontab Component
=================

Crontab provide a php 5.3 lib to create crontab file.

	use Yzalis\Component\Crontab;
	use Yzalis\Component\Job;

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
	$crontab->addJob($job);

	$crontab->write();

You can also parse existing crontab file

	use Yzalis\Component\Crontab;

    $crontab = new Crontab();
    $jobs = $crontab->parseFile($filename);

Resources
---------

You can run the unit tests with the following command:

    phpunit
