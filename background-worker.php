<?php
require ('vendor/autoload.php');

$stream = frankenphp_worker_get_signaling_stream();

function shouldstop(float $timeout = 0): bool
{
    static $signalingStream;
    $signalingStream ??= frankenphp_worker_get_signaling_stream();
    $s = (int) $timeout;

    return match (@stream_select(...[[$signalingStream], [], [], $s, (int) (($timeout - $s) * 1e6)])) {
        0 => false, // timeout - keep going
        false => true, // pipe closed - stop
        default => "stop\n" === fgets($signalingStream),
    };
}

do {
    frankenphp_worker_set_vars([
        'time' => time(),
    ]);
} while(!shouldstop(3));

echo 'ended';