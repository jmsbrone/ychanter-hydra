#!/bin/bash

psql -c "CREATE DATABASE \"ychanter\""
psql -d canape -c "CREATE EXTENSION ltree"
