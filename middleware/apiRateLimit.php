<?php
// use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

function checkRateLimitOfApi()
{
    $ratelimt = new RateLimiterFactory([
        'id' => 'login',
        'policy' => 'token_bucket',
        'limit' => 2,
        'rate' => ['interval' => '1 minutes']
    ], new InMemoryStorage());

    $limiter = $ratelimt->create();
    $limiter->consume(1);
    // if (! $limiter->consume(2)->isAccepted()) {
    //     throw new Exception("Rate limit exceeded");
    // }
    if (false === $limiter->consume(2)->isAccepted()) {
        // throw new TooManyRequestsHttpException();
    }
}

?>