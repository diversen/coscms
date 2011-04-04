#!/bin/sh

# remove old osnet profile modules. 
cd .. && ./coscli.sh module --purge rewrite
./coscli.sh module --purge rewrite_manip


