#!/bin/sh

cd "${0%/*}"

while true
do 
	echo "Running import.php script." >&2
	php import.php
	sleep 60
done

