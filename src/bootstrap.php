<?php

if (!function_exists('frankenphp_worker_set_vars')) {
    function frankenphp_worker_set_vars(array $vars): void
    {
        \Dbu\SharedState\SharedState::setVars($vars);
    }
}

if (!function_exists('frankenphp_worker_get_vars')) {
    function frankenphp_worker_get_vars(string|array $name, float $timeout = 30.0): array
    {
        return \Dbu\SharedState\SharedState::getVars($name, $timeout);
    }
}

if (!function_exists('frankenphp_worker_get_signaling_stream')) {
    function frankenphp_worker_get_signaling_stream()
    {
        [$readStream, $writeStream] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        $handler = function () use ($writeStream) {
            fwrite($writeStream, "stop\n");
        };
        pcntl_signal(SIGINT, $handler);
        pcntl_signal(SIGTERM, $handler);

        return $readStream;
    }
}
