This directory can be used to add locale module files for the CLI
part of the framework.

### Example

Create example. All files need to be ending in .inc

    mv hello_world.inc.example hello_world.inc 

Show help message:

    ./coscli.sh hello_world -h

Say 'hello world'

    ./coscli.sh hello_world --say

Say 'something else' Using an argument

    ./coscli.sh hello_world -s something else
