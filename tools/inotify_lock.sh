#!/bin/bash

cd $1
rm -r .locks 2> /dev/null
mkdir .locks

echo "Watching for MODIFY events and creating lock files."
inotifywait -m -e modify ./ |
while read -r path event; do
  echo $event
  touch .locks/`echo $event | cut -f2 -d " "`.lock
done &

echo "Watching for CLOSE_WRITE events and deleting lock files."
inotifywait -m -e close_write ./ |
while read -r path event; do
  echo $event
  rm .locks/`echo $event | cut -f2 -d " "`.lock 2> /dev/null
done &
