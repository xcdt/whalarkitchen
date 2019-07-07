FROM whalar-kitchen-app-base:v1.1.0


RUN echo ServerName localhost >> /etc/httpd/conf.d/00-ServerName.conf

RUN sed -i 's/^memory_limit.*=.*/memory_limit = 4G/g' /etc/php.ini

RUN mkdir /var/www/whalar-kitchen
RUN chown -R apache:apache /var/www/whalar-kitchen

RUN mkdir /var/log/whalar-kitchen
RUN chown -R apache:apache /var/log/whalar-kitchen

RUN usermod -u 1000 apache && ln -sf /dev/stdout /var/log/httpd/access_log && ln -sf /dev/stderr /var/log/httpd/error_log

RUN ln -sf /etc/whalar-kitchen/httpd/vhost.conf /etc/httpd/conf.d/zz-whalar-kitchen.conf

EXPOSE 80

CMD ["/usr/sbin/httpd", "-D", "FOREGROUND"]
