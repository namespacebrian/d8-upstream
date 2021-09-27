#!/usr/bin/env bash

USE_LANDO="y";

LIGHT_GREEN='\x1B[92m';
NC='\x1B[39m';

echo;
echo "//////////////////////// PRE-FLIGHT ///////////////////////";

#TODAYS_DATE="`date +%Y-%m-%d`";
#echo "Today's date: $TODAYS_DATE";
YMD_HM=$(date +%Y%m%d_%H%M);
echo "YYYYMMDD_HHMM = $YMD_HM";
echo;


# Are we using lando, or drush, or like what
LANDO_CMD=$(which lando);
# echo "Lando cmd: $LANDO_CMD";
if [[ -z $LANDO_CMD ]]; then
  echo; echo "'lando' command not found. We'll just call 'drush'."; echo;
  DRUSH_CMD="drush";
else
  echo "Lando detected.";

  if [[ -z $USE_LANDO ]]; then
    echo -ne "Use lando site? (y/n) ";
    read USE_LANDO;
  fi
  if [[ $USE_LANDO == 'y' || $USE_LANDO == 'yes' ]]; then
    DRUSH_CMD="$LANDO_CMD drush";
  else
    DRUSH_CMD="drush";
  fi
  echo;
fi


# can connect to EIP over network?
echo "Connectivity test...";
if [[ ! -z $(which nc) ]]; then
  nc -z -G 15 apig.eip.osu.edu 443;
  CONNECT_EXIT=$?;
else
  curl -I --connect-timeout 15 https://apig.eip.osu.edu;
  CONNECT_EXIT=$?;
fi


if [[ $CONNECT_EXIT -ne 0 ]]; then
  echo "Unable to open a connection to apig.eip.osu.edu:443.. please check your network connection.";
  exit;
else
  echo "Connectivity test succeeded.";
fi

echo;

# Confirm we can connect to API and authenticate
ACCESS_TOKEN_CMD="$DRUSH_CMD asc-courses-access-token";
echo "Access TOKEN command: $ACCESS_TOKEN_CMD";
eval $ACCESS_TOKEN_CMD;
TOKEN_CMD_EXIT=$?;
# echo; echo;
# echo "Token cmd exit: $TOKEN_CMD_EXIT";
# echo;

if [[ $TOKEN_CMD_EXIT -ne 0 ]]; then
  echo "Unable to get an EIP access token.  Check the API key configuration to make sure it's valid.";
  echo;
  echo;
  exit;
else
  echo "API authentication succeeded.";
  echo;
fi

echo;

# drush asc_courses:pull-all-dorgs
PULL_DORGS_CMD="$DRUSH_CMD asc_courses:pull-all-dorgs";
echo "/////////////////// PULL DORGS FROM API ///////////////////";
echo "Pull dorgs command: $PULL_DORGS_CMD";
eval $PULL_DORGS_CMD;
echo;


# drush asc_courses:process-api-data
echo "/////////////////// PROCESS API DATA //////////////////////";
PROCESS_DATA_CMD="$DRUSH_CMD asc_courses:process-api-data";
echo "Process api data command: $PROCESS_DATA_CMD";
eval $PROCESS_DATA_CMD;
echo;



# # drush sql-dump --tables-list=asc_courses_api_data,asc_courses_processed > asc_courses_data.sql
echo "/////////////////// EXPORT SQL DATA ///////////////////////";
COURSES_DATA_FILE="asc_courses_data_${YMD_HM}.sql"
echo "Courses data file: $COURSES_DATA_FILE"

# SQL_DUMP_CMD="$DRUSH_CMD sql-dump --tables-list=asc_courses_api_data,asc_courses_processed > $COURSES_DATA_FILE"
SQL_DUMP_CMD="$DRUSH_CMD sql-dump --tables-list=asc_courses_processed > $COURSES_DATA_FILE"

echo "SQL dump command: $SQL_DUMP_CMD";
eval $SQL_DUMP_CMD;
echo;


