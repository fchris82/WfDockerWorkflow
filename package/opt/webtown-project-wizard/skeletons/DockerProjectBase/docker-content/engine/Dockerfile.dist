FROM ubuntu:16.04

ARG TIMEZONE
ENV LANG en_US.UTF-8
RUN apt-get clean && apt-get update \
    && apt-get install -y locales wget curl software-properties-common ssh build-essential gosu \
       curl git libmcrypt-dev libicu-dev libxslt-dev libxml2-dev unzip libfontconfig libglib2.0-bin \
    && locale-gen en_US.UTF-8 \
    && mkdir -p /home/user/.ssh \
    && mkdir -p /run/php \
    && usermod -d /home/user www-data \
    && chown www-data /home/user \
    && touch /home/user/.gitignore && git config --global core.excludesfile '/home/user/.gitignore' \
    && ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && rm -rf /var/lib/apt/lists/*

#RUN ssh-keyscan bitbucket.org > /root/.ssh/known_hosts

# RUBY
#ARG RUBY_MAJOR
#ARG RUBY_VERSION
#ARG RUBYGEMS_VERSION
#ARG BUNDLER_VERSION
#ARG REDCARPET_VERSION
#ARG HOLOGRAM_VERSION
#ARG ROUGE_VERSION
#ARG SASS_VERSION
#
#RUN add-apt-repository -y ppa:brightbox/ruby-ng \
#    && apt-get update && apt-get install -y ruby$RUBY_MAJOR ruby-dev \
#    && gem update --system "$RUBYGEMS_VERSION" \
#    && gem install bundler --version "$BUNDLER_VERSION" \
#    && gem install redcarpet --version "$REDCARPET_VERSION" \
#    && gem install hologram --version "$HOLOGRAM_VERSION" \
#    && gem install rouge --version "$ROUGE_VERSION" \
#    && gem install sass --version "$SASS_VERSION"

# PHP
ARG PHP_VERSION
ARG PHP_REQUIRES_FILE=./config/php_requires.txt
COPY $PHP_REQUIRES_FILE /tmp/php_requires

RUN add-apt-repository -y ppa:ondrej/php \
    && apt-get update && apt-get install -y php${PHP_VERSION}-fpm php${PHP_VERSION} \
    && apt-get purge -y --remove apache2 \
    && apt-get install -y `grep -vE "^\s*#" /tmp/php_requires | sed -r "s/([0-9A-Za-z]*)/php${PHP_VERSION}-\1/g" | tr "\n" " "` \
    && apt-get install -y php-xdebug \
    && sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php/${PHP_VERSION}/fpm/php-fpm.conf \
    && sed -e 's#error_log = .*#error_log = /var/log/php-fpm.log#' -i /etc/php/${PHP_VERSION}/fpm/php-fpm.conf \
    && sed -e 's/listen = .*/listen = \[::\]:9003/' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && sed -e 's/;listen\.owner/listen.owner/' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && sed -e 's/;listen\.group/listen.group/' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && sed -e 's/;catch_workers_output/catch_workers_output/' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && sed -e 's/;php_admin_flag[log_errors]/php_admin_flag[log_errors]/' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && sed -e 's#;php_admin_value[error_log] = .*#php_admin_value[error_log] = /var/log/php/www.log#' -i /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && rm -rf /var/lib/apt/lists/*

## NODE JS
#RUN curl -sL https://deb.nodesource.com/setup_6.x | bash - \
#    && apt-get install -y nodejs \
#    && rm -rf /var/lib/apt/lists/* \
#    && npm install -g grunt-cli bower gulp uglify-js uglifycss

VOLUME /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

VOLUME /var/www
WORKDIR /var/www
