@echo off
rsync -avz --rsh="ssh -p 22 -i %HOMEPATH%/.ssh/id_rsa" admin_ftp_11@89.253.226.187:/var/www/vhosts/intraceuticals.ru/httpdocs/shared/ ../web/shared/
rsync -avz --rsh="ssh -p 22 -i %HOMEPATH%/.ssh/id_rsa" admin_ftp_11@89.253.226.187:/var/www/vhosts/intraceuticals.ru/httpdocs/py/ ../web/py/
rsync -avz --rsh="ssh -p 22 -i %HOMEPATH%/.ssh/id_rsa" admin_ftp_11@89.253.226.187:/var/www/vhosts/intraceuticals.ru/httpdocs/in/ ../web/in/
rsync -avz --rsh="ssh -p 22 -i %HOMEPATH%/.ssh/id_rsa" admin_ftp_11@89.253.226.187:/var/www/vhosts/intraceuticals.ru/httpdocs/sl/ ../web/sl/
rsync -avz --rsh="ssh -p 22 -i %HOMEPATH%/.ssh/id_rsa" admin_ftp_11@89.253.226.187:/var/www/vhosts/intraceuticals.ru/httpdocs/td/ ../web/td/
pause