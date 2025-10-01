#!/bin/bash
mkdir -p static/uploads
chmod -R 755 .
chmod 777 static/uploads
php -S 0.0.0.0:${PORT:-10000} -t .
