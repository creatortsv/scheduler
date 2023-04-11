# Scheduler
Stand-alone package which provides functionality to schedule script execution.

## Installation
```shell
composer install creatortsv/scheduler
```

---

## Getting Started
It's easy to start using the scheduler.
```php
use Creatortsv\Scheduler\Registrar\ScheduleRegistrar;
use Creatortsv\Scheduler\Scheduler;

$job = function (): void {
    /* your job logic here ... */
}

$scheduler = new Scheduler();
$scheduler->getRegistrar()->register($job);
$scheduler->run();
```
The example above just registered simple closure function as a job with default cron expression `* * * * *` (which means that it will be executed every minute) and run it.

## Cron Expression
> For complete documentation of the Cron Expression object visit https://github.com/dragonmantank/cron-expression

Each job has its own cron expression object. The `register` method of the `ScheduleRegistrarInterface` returns a cron expression object with default value `* * * * *`. So you can change it after your job had been registered.
```php
/* Initializing scheduler ... */

$expr = $scheduler->getRegistrar()->register($job);
$expr->setExpression('0 */2 * * *');

/* every two days at 00:00 */
```

## Schedule Provider
Sometimes you want to register several jobs that could be grouped by specific logic. In that case Schedule Provider is more useful. 
```php
use Creatortsv\Scheduler\Provider\ScheduleProviderInterface;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;

class MyScheduleProvider implements ScheduleProviderInterface
{
    public function boot(ScheduleRegistrarInterface $registrar): void
    {
        // Register specific jobs here ...
    }
}
```
Don't forget to add your provider via scheduler object
```php
/* Initializing scheduler ... */

$scheduler->add(new MyScheduleProvider());
```

## Run Scheduler
```php
/* Initializing scheduler ... */

$scheduler->run();
```
By default, the Scheduler is running with the current `DateTime` object for each job, that means it will create the `DateTime` object and each Cron Expression object using the same timestamp determine if it is the time to execute job or not.

But sometimes you need run your schedule on the specific timestamp, testing for example. Use `Scheduler::at` method to change default behaviour.
```php
/* Initializing scheduler ... */

$date = new DateTime();
$date->modify('-3 days');

$scheduler->at($date)->run();
```
---
## Advanced Usage
Sometimes you may want to control which jobs must be executed in addition of default behaviour. Only one instance of the registered job should be running at the current time, for example.

In that cases you can use `Scheduler::boot` method instead of `Scheduler::run`. The `boot` method returns `ScheduleIterator` object, each item is a job instance which must be executed by the given timestamp and each key is cron expression of given job.

Let's imagine that you have some service which determines that job is already being executed
```php
class JobManager
{
    public function isReleased(SpecificJobInterface $job): bool
    {
        /* your logic is here ... */
    }
}
```

So you can use this service
```php
/* Initializing $scheduler and your $jobManager ... */

foreach ($scheduler->boot() as $job) {
    if ($job instanceof SpecificJobInterface) {
        $jobManager->isReleased($job) && $job();
    }
}
```

But there is the best way to do so
```php
/* Initializing $scheduler and your $jobManager ... */

$scheduler->getModerator()->register($jobManager->isReleased(...));
$scheduler->run();
```

And you shouldn't have to worry about if the job has different type of callable object