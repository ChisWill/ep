@echo off

@setlocal

set EP_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%EP_PATH%ep" %*

@endlocal
