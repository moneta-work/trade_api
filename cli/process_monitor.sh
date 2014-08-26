#!/bin/bash
#cli_*重启&监控脚本
#日志文件
file_name="/var/log/cli/php_coinin.log"
proc_date=`date "+%Y%m%d"`
#check
proc_name=("freeybcin.php" "ybyin.php")

case "$1" in
    check)
        #单纯检查脚本是否存在
        for proc in ${proc_name[@]}
        do
            proc_space=${proc//,/ }
            number=`ps -ef | grep "$proc_space" | grep -v grep | wc -l`
            # 判断进程是否存在
            if [ $number -eq 0 ]
            then
                cd /home/jcs/trade_api/cli && nohup php $proc &
                pid=`ps -ef | grep "$proc_space" | grep -v grep | awk '{print $2}'`
                # 将新进程号和重启时间记录
                echo ${pid}, `date` >>  $file_name
            fi
        done
		;;
    *)
        echo "it's need enter the param: check|restart"
        exit 1
		;;
esac
exit 0
