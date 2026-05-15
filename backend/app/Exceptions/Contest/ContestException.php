<?php

namespace App\Exceptions\Contest;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ContestException extends HttpException
{
    public function __construct(string $message, int $statusCode = 422)
    {
        parent::__construct($statusCode, $message);
    }

    public static function notJoinable(): self
    {
        return new self('This contest is not open for joining.', 422);
    }

    public static function alreadyJoined(): self
    {
        return new self('You have already joined this contest.', 409);
    }

    public static function notJoined(): self
    {
        return new self('You have not joined this contest.', 403);
    }

    public static function alreadySubmitted(): self
    {
        return new self('You have already submitted a result for this contest.', 409);
    }

    public static function disqualified(): self
    {
        return new self('You have been disqualified from this contest.', 403);
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Cannot transition contest from '{$from}' to '{$to}'.", 422);
    }

    public static function cannotDeleteActive(): self
    {
        return new self('An active contest cannot be deleted.', 422);
    }

    public static function notPaused(): self
    {
        return new self('Contest is not currently paused.', 422);
    }

    public static function alreadyFinished(): self
    {
        return new self('Contest is already finished.', 422);
    }
}
