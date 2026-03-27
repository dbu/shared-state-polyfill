Experimenting with a polyfill for the frankenphp shared state concept.

See https://github.com/php/frankenphp/pull/2287/

## Current state

Run those in 2 consoles:
```
BACKGROUND_NAME=test php background-worker.php

watch --interval 1 php client.php
```

To see the startup timeout, start the client first and the background-worker second.

Test scoped workers and client processes, run these in 3 separate consoles:
```
BACKGROUND_SCOPE=A BACKGROUND_NAME=test php background-worker.php
# will work
BACKGROUND_SCOPE=A watch --interval 1 php client.php

# this will timeout after 30 seconds
BACKGROUND_SCOPE=B watch --interval 1 php client.php
```

* Atomic write of data to a file, reading state with a timeout.
* Worker name and context defined via environment variables. I don't see how we can use magic here.
* Signal handling that works for Linux.

## Open Questions and TODO

* Add phpunit tests for SharedState class
* Tried out with /tmp but should be configurable and on symfony default to var/ . Its not really a cache, the background workers don't expect the data to randomly be deleted, which would seem an action that should be expected on /tmp or var/cache.
* Is it correct to usleep waiting for the file? Is 1000 microseconds a good interval?

* We should abstract the storage to also support e.g. APCu, Memcache, Redis etc
* Signalling stream for non-Linux systems

* Can we detect a crashed worker? Once the files exist, data just no longer updates when worker is crashed.

## Limits
I don't think there is a realistic way to start background workers in a simple configuration file or even on demand like with frankenphp. I would expect the user has to configure all background workers upfront, with the right environment variables to make them write to the correct place. But i think with the polyfill, background workers written for https://github.com/php/frankenphp/pull/2287 can be started and used without changes to the actual code of the workers.

Maybe we can offer a symfony command to dump a list of workers that needs to be configured. I think nikolas proposed some attribute to mark workers...