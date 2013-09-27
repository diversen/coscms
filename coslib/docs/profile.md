Profile
=======

The profile class defals with creating profiles for install. And installing
from profiles. When creating a profile it will be placed in the `profiles/` dir.
There is some profiles included for some sites I have made. They really does not
contain much: When creating a profile with

    ./coscli.sh profile --create my_profile

A directory will be made called `my_profile` inside `profiles` containing 
all ini settings for all modules in their current version. You can specify
`--master` in order to make the profile from all modules git master. 

Then it generates a `profile.inc` file, which contains public and private 
git info extracted from all install.inc files.  

