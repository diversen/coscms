This is the profiles dir

The dir will be used for making profiles which describes an install of distinct
type of sites. So far there is made room for three profiles for testing.

Default is the default install profile.
Bartels is an example site with a gallery and an event calendar
Coscms is the coscms.org install profile.

When creating an install profile use the command:
./coscli.sh profile --create-profile my-profile

This will create a profile from current site settings
All modules current ini file settings and config/config.ini settings
It will export all modules as an array in module.php where $_PROFILE_MODULES
holds all info about the modules at the time when the profile were created.

Finally it will export which template is being used.