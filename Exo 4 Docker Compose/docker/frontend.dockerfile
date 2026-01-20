FROM nginx:alpine

RUN apk add --no-cache gettext

COPY frontend/src/index.html.template /usr/share/nginx/html/index.html.template
COPY frontend/src/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
