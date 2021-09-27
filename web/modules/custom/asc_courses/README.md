# Drupal 8 re-implementation of the 'asc_courses' module

Fetch course data from EIP and generate Drupal nodes from it

## Caveats

This was originally developed to support Pantheon's "Secure Integration" service, but that use case is no longer being actively tested.  The code may or may not still work in that environment.  SI environment constants are hard-coded.

The current approach involves running an internal Drupal site which pulls all course data into a custom database table which is then exported and loaded to every production Drupal site that needs the data.  The admin UI then uses the data in the database.  Only the interal Drupal site needs to be able to contact EIP.. the production Drupal sites consuming the data don't need to access EIP directly.

The update process is not fully automated yet.

The module assumes the presence of a "Course" content type with the appropriate fields to receive the course content.  The module doesn't create (or currently document) the content type.

## Usage

### Site that accesses EIP directly

- Enable the module
- Navigate to **Configuration -> Content Authoring -> ASC Courses Settings -> API Settings**
- Enter EIP consumer key(s) and secret(s) and select an EIP environment
- Run `drush asc_courses:pull-all-dorgs` to fetch raw course data from EIP and store locally
- Run `drush asc_courses:process-api-data` to process the raw course data into the `asc_courses_processed` database table
- Run `drush sql-dump --tables-list=asc_courses_api_data,asc_courses_processed > asc_courses_data.sql` to export the gathered data to a sql file
- Load the `asc_courses_data.sql` file to each site that wants to use the data.  I use [this pair of scripts](https://gist.github.com/weaver299/bf8eb877146a5deeab5f41392db65468) to load the data to every Pantheon site using one of my custom upstreams.

### Sites using the course data

- Enable the module
- Have the "Course" content type in place.
- Navigate to **Configuration -> Content Authoring -> ASC Courses Settings**
- Select D-Org numbers (subjects) and/or specific EIP course IDs to import as Drupal nodes
- The "Course Search" tab can be used for looking up EIP course IDs

### TODO

- Clean up process-api-data output
- Consider not processing/updating unused fields (e.g. crse_offer_nbr, acad_career)
- Store separate dates for last time course data (checked) was checked and last time content changed (changed)

