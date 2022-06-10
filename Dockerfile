FROM httpd:bullseye
MAINTAINER Lacey-Anne Sanderson <laceyannesanderson@gmail.com>

USER root

COPY . /app

RUN chmod -R +x /app && apt-get update 1> ~/aptget.update.log \
  && apt-get install git curl gcc make libpng-dev --yes -qq 1> ~/aptget.extras.log

RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
	&& apt install nodejs

WORKDIR /app

RUN npm install && npm run build

RUN rm -r /usr/local/apache2/htdocs \
	&& cd /usr/local/apache2 \
	&& ln -s /app/dist htdocs
