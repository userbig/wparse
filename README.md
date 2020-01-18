# WPARSE

This is CLI multithreaded wars parser for [EVE Online](https://www.eveonline.com/). Based on [pthreads](https://www.php.net/manual/en/book.pthreads.php) extension for php.
<br>
At this moment this is standalone app.

### Info

- This tool can parse all wars (at this moment 660692) in 300 threads for 12 minutes
- Active wars (first you need to populate your db for that of course) in 3 threads for a minute


## Special requirements

- PHP-ZTS =>7.3.2
- PostgreSQL
- At least 3 cores and 2g RAM (for 300 threads)
- PDO PHP Extension


## Installation

TODO


## Getting started


Copy and rename `.env.example` to `.env` and populate fields with your data.

###### To store ALL wars to fresh database run command

`php app wars:all`

###### Check active wars

`php app wars:active`



## Typical data

```
 aggressor_id |                           aggressor                            |                           allies                            |      declared       | defender_id |                            defender                            |      finished       | war_id | mutual | open_for_allies |       started       |   last_api_update   
--------------+----------------------------------------------------------------+-------------------------------------------------------------+---------------------+-------------+----------------------------------------------------------------+---------------------+--------+--------+-----------------+---------------------+---------------------
     98036605 | {"corporation_id":98036605,"isk_destroyed":0,"ships_killed":0} | [{"corporation_id":98120136},{"corporation_id":1721412068}] | 2012-07-13 13:45:00 |    98079171 | {"corporation_id":98079171,"isk_destroyed":0,"ships_killed":0} | 2012-07-16 14:37:00 | 221829 | f      | f               | 2012-07-14 13:45:00 | 2020-01-17 07:32:41

```

`aggressor` and `defender` typical json. `allies` are json array elements

PostgreSQL can query through json objects with ease without any (noticeable) perfomance impact

[About json in PostgreSQL](https://www.postgresql.org/docs/9.3/functions-json.html)


## TODOs

- implement progress bar 
