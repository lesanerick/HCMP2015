@echo OFF
set current=%~dp0
set old_cnf=resources\mysql\old\
set old_cnf_file=resources\mysql\old\my.ini
set new_cnf=resources\mysql\new\
set new_cnf_file=resources\mysql\new\my.ini
set target_cnf=C:\xampp\mysql\bin\
set target_cnf_file=C:\xampp\mysql\bin\my.ini
net stop Apache2.4
net stop MySQL
move "%target_cnf_file%" "%current%%old_cnf%"
xcopy /s "%current%%new_cnf_file%" "%target_cnf%"
net start Apache2.4
net start MySQL
C:\xampp\mysql\bin\mysql.exe -u root hcmp_rtk<"%current%"10639.sql
net stop Apache2.4
net stop MySQL
del "%target_cnf_file%"
move "%current%%old_cnf_file%" "%target_cnf%"
net start Apache2.4
net start MySQL
pause