#!/bin/bash
find /tmp/systemd-private-bea753a5f73d4c45bde58f2732d11ffb-httpd.service-Z8CQ4l/tmp -type d -ctime +5 | xargs rm -Rf
find /tmp/systemd-private-bea753a5f73d4c45bde58f2732d11ffb-httpd.service-Z8CQ4l/tmp -type f -ctime +5 | xargs rm -Rf
