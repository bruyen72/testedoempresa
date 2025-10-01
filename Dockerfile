FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN mkdir -p static/uploads && \
    chmod -R 755 /app && \
    chmod 777 static/uploads

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
