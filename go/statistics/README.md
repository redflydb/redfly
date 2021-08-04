The statistics reporting utility
--------------------------------

This utility reports the statistics of all the entities together and each one of all the ones by separate in several text files since the last update of the REDfly database.

- To use, edit `main.go` to update the `dsn` constant with the appropriate database credentials (see example in the comments therein), then compile and run. The statistics will be printed to `STDOUT`.
