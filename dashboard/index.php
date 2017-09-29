<?php

/**
 * This is the clock interface. It's really simple, you write it once, use it anywhere.
 * Cool extra things you can do:
 *      - have it return custom value objects
 *      - separate method for currentDate() without time part
 */
interface Clock
{
    public function currentTime(): DateTimeImmutable;
}

/**
 * SystemClock just relies on the current system:
 *
 * Cool extra things you can do:
 *      - force a timezone regardless of the current system settings
 *      - settle disputes about DateTimeImmutable vs DateTime in your codebase ONCE AND FOR ALL, MWAHHAHA
 */
class SystemClock implements Clock
{
    public function currentTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}

/**
 * Mock Clock just returns a date you set.
 *
 * Cool extra things you can do:
 *
 *      - mock current time in unit tests
 *      - shortcut constructors like MockClock::isCurrently('10:41')
 *      - have a setter to advance the clock as the test progresses
 */
class MockClock implements Clock
{
    private $fixedTime;

    public function __construct(DateTimeImmutable $dateTime)
    {
        $this->fixedTime = $dateTime;
    }

    public function currentTime(): DateTimeImmutable
    {
        return $this->fixedTime;
    }
}

// Okay, how/why would we use it? Okay, consider this off-the-cuff use case{
class DaysUntilBirthdayCalculator
{
    /** @var Clock */
    private $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function daysLeft(DateTimeImmutable $myBirthday): int
    {
        $currentTime = $this->clock->currentTime();
        if ($currentTime > $myBirthday) {
            throw new LogicException("Hey, you already had that birthday!");
        }

        return (int)$myBirthday->diff($currentTime)->format("%a");
    }
}

// Cool, number of days until my birthday!
$myBirthday = new DateTimeImmutable('Nov 10, 2016');
$calculator = new DaysUntilBirthdayCalculator(new SystemClock());
var_dump($calculator->daysLeft($myBirthday));

// Oh wait, I wrote a unit test for this last month but it constantly fails!
var_dump($calculator->daysLeft($myBirthday) === 34);

// No worries, let's use a mock clock. Now we can write unit tests that we can rely on always!
$calculator = new DaysUntilBirthdayCalculator(
    new MockClock(new DateTimeImmutable('2016-10-18'))
);
var_dump($calculator->daysLeft($myBirthday) === 23);
