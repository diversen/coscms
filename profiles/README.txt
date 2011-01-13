This is the profiles dir

The dir will be used for making profiles which describes an install of distinct
type of sites. So far there is made room for three profiles for testing.

osnet is the default install profile.

For creating / adding / delting see: 

./coscli.sh profile -h

This happens when you create a profile:

All modules current ini file settings and config/config.ini settings
will be exported as an array to profile/yourprofile/profile.inc where 
$_PROFILE_MODULES holds all info about the modules at the time when 
the profile were created.

Finally it will export which templates is being used and which is 
enabled.
