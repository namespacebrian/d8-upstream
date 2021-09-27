#!/usr/bin/env bash

# Example usage:
#   echo "select distinct type, count(*) from node group by 1 order by 2 desc;" > node_counts.sql
#   ./query_all.sh node_counts.sql live | tee node_counts.txt

UPSTREAM='b9baf7af-eb2c-4db5-81e6-32d3d9042572';

# Check command line arguments
if [[ -z $1 ]]; then
  echo; echo "Usage: $0 <query_file> [environment]";
  echo; echo;
  exit 1;
elif [[ ! -f $1 || ! -r $1 ]]; then
  echo; echo "The filename '$1' doesn't exist, isn't readable, or isn't an ordinary file.";
  echo; echo;
  exit 1;
fi


# colors
LIGHT_GREEN='\x1B[92m';
LIGHT_RED='\x1B[91m';
NC='\x1B[39m';


# Default to 'dev' environment if none was specified
if [[ -z $2 ]]; then
  echo; echo "No environment specified, assuming dev.."; echo;
  ENV='dev';
else
  ENV=$2;
fi


# ALL sites
SITES=$(terminus site:list --upstream=$UPSTREAM --fields=name --format=string | sort);

# Non-sandbox sites only
# SITES=$(terminus site:list --upstream=$UPSTREAM --fields=name --format=string --filter=plan_name!=Sandbox | sort);


for SITE_NAME in $SITES; do
  echo -e "${LIGHT_GREEN}=== $SITE_NAME ===${NC}";

  # if [[ ! -f ./backup_fresh.sh || ! -x ./backup_fresh.sh ]]; then
  #   echo "./backup_fresh.sh isn't an executable script... assuming all backups are stale..";
  #   BACKUP_FRESH=1;
  # else
  #   # BACKUP FRESHNESS SCRIPT: https://gist.github.com/weaver299/46257300e53fe50b2a0b929ab721860e
  #   # Check if backup is "fresh" "enough"... (only if [[ $ENV == 'live' ]] maybe?)
  #   ./backup_fresh.sh $SITE_NAME.$ENV;
  #   BACKUP_FRESH=$?; # capture exit code
  # fi

  # if [[ $BACKUP_FRESH -gt 0 ]]; then
  #   # 0 means "fresh enough", else the script returns the age in seconds
  #   echo "Backup is stale - Creating a new one...";
  #   # echo "terminus backup:create $SITE_NAME.$ENV --keep-for=30 --element=db";
  #   terminus backup:create $SITE_NAME.$ENV --keep-for=30 --element=db;
  # fi

  # Wake site  (only if [[ $ENV == 'live' ]] if you have no sandboxes?)
  terminus env:wake $SITE_NAME.$ENV;

  if [[ -z $(which pv) ]]; then
    echo "'pv' command unavailable, using 'cat' instead...";
    cat $1 | $(terminus connection:info --fields=mysql_command --format=string $SITE_NAME.$ENV);
  else
    echo "Using 'pv' command...";
    pv $1 | $(terminus connection:info --fields=mysql_command --format=string $SITE_NAME.$ENV);
  fi

  echo;
done

