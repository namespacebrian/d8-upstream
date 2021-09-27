#!/usr/bin/env bash

if [[ -z $1 ]]; then
  echo; echo "Usage: $0 <site.env>";
  echo; echo;
  exit 1;
fi

# MacOS compatibility....
if [[ "$OSTYPE" == *"darwin"* ]]; then
  # echo "This is MacOS.  OSTYPE[$OSTYPE]";
  if [[ -z `which gdate` ]]; then
    echo; echo "'gdate' command not found.";
    echo -n "The GNU version of the 'date' command is needed on MacOS. ";
    echo "It is available as the 'gdate' command via the 'coreutils' Homebrew package.";
    if [[ -z `which brew` ]]; then
      echo;
      echo "Homebrew is NOT installed. Homebrew and the 'coreutils' package need to be installed to proceed. Stopping.";
      echo "Visit https://brew.sh/ for more information.";
    else
      echo; echo "Try installing the 'coreutils' package -- brew install coreutils";
    fi
    echo; echo;
    exit 1;
  else
    DATE_CMD='gdate';
  fi
elif [[ "$OSTYPE" == *"linux"* ]]; then
  # echo "This is Linux - OSTYPE[$OSTYPE]. Assuming 'date' command is available and GNU.";
  DATE_CMD='date';
else
  echo; echo "Unable to determine OS type - \$OSTYPE[$OSTYPE] contains neither 'linux' nor 'darwin'. Stopping.";
  echo; echo;
  exit 1;
fi


MAX_AGE=3600;  # One hour = "fresh enough"

# Returns unix timestamp with decimal
BACKUP_DATE_UNIX=`terminus backup:info --field=date --format=string $1`;

if [[ -z $BACKUP_DATE_UNIX ]]; then
  BACKUP_DATE_UNIX=0;   # lol never!
fi

BACKUP_DATE_UNIX=${BACKUP_DATE_UNIX%.*};  # Strip decimal
BACKUP_DATE_FRIENDLY=$($DATE_CMD -d @$BACKUP_DATE_UNIX);
echo "Backup date: $BACKUP_DATE_FRIENDLY";

NOW=`$DATE_CMD +%s -u`;
BACKUP_AGE=`expr $NOW - $BACKUP_DATE_UNIX`;
echo "Backup age: $BACKUP_AGE seconds";

if [[ $BACKUP_AGE -gt $MAX_AGE ]]; then
  # echo "Backup is stale - $BACKUP_DATE_FRIENDLY - $BACKUP_AGE seconds ago.";

  # By convention, a non-zero exit code indicates an error condition.
  # We will return the backup age in seconds, since it is stale.
  exit $BACKUP_AGE;
else
  echo "Backup is fresh enough - $BACKUP_DATE_FRIENDLY - $BACKUP_AGE seconds ago.";

  # By convention, exit code 0 means "success", so we will return 0
  # to indicate success -- the backup is fresh
  exit 0;
fi

