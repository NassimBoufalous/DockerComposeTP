#!/bin/sh
envsubst '$BACKEND_URL $BACKEND_PORT' \
  < /usr/share/nginx/html/index.html.template \
  > /usr/share/nginx/html/index.html

exec nginx -g 'daemon off;'
