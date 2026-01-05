#!/bin/bash
(crontab -l | grep -v "user/bin/qwerty /home/u115791035/domains/madebymaa.store/public_html/deshboard/artisan dm:disbursement") | crontab -

(crontab -l | grep -v "user/bin/qwerty /home/u115791035/domains/madebymaa.store/public_html/deshboard/artisan restaurant:disbursement") | crontab -

