# Tools

## Aliases

There are several aliases defined in the aliases.sh file for convenience.

Run `source aliases.sh` to import them.

* `p` runs all phpunit tests
* `pc` runs all non-db phpunit tests and generates a code coverage report into the `coverage/` folder
* `cov-check` verifies code coverage percentage (after running `pc`)
* `cs-fix` fixes the syntax of all php files
* `updb` upgrades your db to the latest schema version
* `my` starts a command-line mysql client with the current app config parameters
* `pd` generates php documentation into the `output/` folder
* `it` runs a small set of integration tests
* `hb` runs humbug tests

## Database upgrades

To create a new database upgrade script, first determine the current version hash
by running `updb`.

Then, create a file with the hash name in the `sql/upgrades` folder, e.g.
`sql/upgrades/b7a29a3172747dc137fc1adbfbea6cea.sql` (don't forget `.sql`!).

The upgrade script will detect the new file and run any SQL contained within it
to upgrade your schema.

Before applying each upgrade, a compressed backup of the database is kept in the 
`sql/backups` folder.
