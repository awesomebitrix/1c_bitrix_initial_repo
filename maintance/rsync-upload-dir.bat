@echo off
SET USER=test
SET HOST=test
SET REMOTE_UPLOAD_DIR='./public_html/current/web/upload'
SET LOCAL_DEST_DIR='../web/'
SET SSH_PORT=22
SET PATH_TO_PRIVATE_KEY=%HOMEPATH%/.ssh/id_rsa
SET RSYNC=win_utils\cwRsync_5.5.0_x86_Free\bin\rsync
SET RSYNC_SSH=win_utils\cwRsync_5.5.0_x86_Free\bin\ssh
%RSYNC% -avz --rsh="%RSYNC_SSH% -p %SSH_PORT% -i %PATH_TO_PRIVATE_KEY%" %USER%@%HOST%:%REMOTE_UPLOAD_DIR% %LOCAL_DEST_DIR%
pause