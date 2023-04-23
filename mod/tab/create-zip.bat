echo off
set zipName=mod_tab
set pluginName=tab

rem remove the current 
del %zipName%

rem zip the folder except the folder .git
"c:\Program Files\7-Zip\7z.exe" a -mx "%zipName%.zip" "..\moodle-mod_tab\"  -mx0 -xr!".git"  -xr!"create-zip.bat" 

rem set the plugin name
"c:\Program Files\7-Zip\7z.exe" rn "%zipName%.zip" "moodle-mod_tab\" "%pluginName%\"

pause