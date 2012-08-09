This directory can be used to create Standalone shell modules 
with the CLI part of the CosCMS framework. 

### Install

We use the Pear Console Commandline class

    pear install Console_Commandline

We use the CosCMS framework

    git clone git://github.com/diversen/coscms.git

### Example

Move into folder where you can place your commands: 

    cd ./coscms/coslib/shell_base/locale

Complete command example: 

https://github.com/diversen/coscms/blob/master/coslib/shell_base/locale/hello_world.php

Create example. All files need to be ending in .inc

    mv hello_world.php hello_world.inc

cd back to main path: 

    cd ../../../

Show help message:

    ./coscli.sh hello_world -h

Say 'hello world'

    ./coscli.sh hello_world --say

Say 'something else' Using an argument

    ./coscli.sh hello_world -s 'something else'

That's it. 
