The site's exported configuration lives here, and is committed with the code.

After installing, run `drush config:export` and commit the result. Every deploy
then runs `drush deploy`, which imports it. Until something is exported here,
scripts/deploy.sh only runs database updates, since importing an empty
directory would delete the site's configuration.
