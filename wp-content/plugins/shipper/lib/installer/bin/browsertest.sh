#!/usr/bin/bash

sudo rm -rf ./shipper-working
rm ./test.zip

mkdir ./shipper-working && chmod -R a+w ./shipper-working
cp ./tests/data/test.zip .

grunt concat
