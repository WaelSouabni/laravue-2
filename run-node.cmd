@ECHO off
SETLOCAL
CALL :find_dp0

IF EXIST "%dp0%\bash.exe" (
  SET "_prog=%dp0%\bash.exe"
) ELSE (
  SET "_prog=bash"
  SET PATHEXT=%PATHEXT:;.JS;=;%
)

"%_prog%"  "%dp0%\node_modules\run-node\run-node" %*
ENDLOCAL
EXIT /b %errorlevel%
:find_dp0
SET dp0=%~dp0
EXIT /b
