#!/bin/bash

rootpassword=$(echo $RANDOM | md5sum | head -c 20)
database="ychanter_hydra_$(echo $RANDOM | md5sum | head -c 8)"

# Creating configs
echo "DB_ROOT_PASSWORD=$rootpassword" > ".env"
echo "CREATE DATABASE \"$database\";" > docker/db/init.sql
echo "<?php return ['database'=>'$database','user'=>'postgres','password'=>'$rootpassword'];" > config/db.php
