# PHPAGI `<---->` PAMI

"The connection of PHPAGI and PAMI" ⛓

## Description

This adapter is intended to function as a mostly drop-in replacement for
[PHPAGI](http://phpagi.sourceforge.net/)'s AMI client. If you are running into
some limitations in PHPAGI but have a lot of code that depends on its API,
perhaps this may be of some help :)

Everyone else will probably want to just use
[PAMI](http://marcelog.github.com/PAMI) directly!

The test suite does rely on PHP 7 features, but the library itself should have
the same requirements as PAMI (currently PHP >= 5.3.3).

There are several ways in which this adapter doesn't function exactly like
PHPAGI, and some more likely to require code changes than others.

## Differences from PHPAGI

- All AMI events and responses have lowercase "keys". This is a characteristic
  of PAMI.

- AMI events and responses have additional subarrays, that PAMI makes available
  but PHPAGI does not.

    - Events:

        - `__channelvariables`: Array of channel variables that have been configured
          to be sent with "channel-related" AMI events using the `channelvars` key
          in `manager.conf`.

    - Responses:

        - `__eventlist`: Array of events sent in response to the requested action.
          For example, Asterisk can generate multiple Status events in response to
          the Status action. In my experience PHPAGI does not handle these events at
          all, so they are made available in the adapter via this new array key.

- Due to the fact that PAMI currently puts the TCP socket in nonblocking mode,
  the `wait_response` function behaves very differently in the adapter than in
  PHPAGI. If running `wait_response` in something like a `while (true)` loop, it
  will most likely be necessary to add some kind of sleep between iterations.

- The `log` function cannot log to the Asterisk console, because there is no
  parent AGI. Therefore this function is a wrapper around `error_log`.

- The adapter client's constructor allows the user to pass additional
  PAMI-specific options, which override any PHPAGI options. For example, the user
  can define a PSR-3 compatible logger implementation to be used by PAMI.  The
  `connect_timeout` and `read_timeout` config options may be of particular
  interest.

- The adapter client can throw PAMI exceptions (it does not throw any of its own
  exceptions, but it also does not catch PAMI exceptions).

- The `add_event_handler` function accepts any callable, not only functions.
