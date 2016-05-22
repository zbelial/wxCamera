git add -A;
#git commit -m $(date +%Y%m%d);
#git push -u origin master;
read -p "此次上传:"  val
##echo $val
git commit -m $val
git push origin master
ssh -p 22 root@numberwolf.top 'cd /var/www/html/wxCamera && git reset --hard && git pull origin master && cd .. && ./write_ddtc_db.sh && chmod -R 777 wxCamera/';  
