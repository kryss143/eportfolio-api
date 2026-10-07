#!/bin/sh
# Supervisor: runs the PHP built-in server (Laravel) and nginx together, and
# restarts whichever process dies so a crash never leaves the container
# permanently broken.
#
# Key points:
# - Health check sends a REAL HTTP request. `nc -z` opens a bare TCP
#   connection with no payload, which the PHP built-in server logs as
#   "Malformed HTTP request" and can abort on some versions.
# - nginx runs in the background; the loop restarts whichever process died.
#   The previous version blocked forever on `nginx -g 'daemon off;'`, so a
#   dead PHP server was never noticed or restarted.

PORT=9000

start_php() {
    php artisan serve --host=127.0.0.1 --port=$PORT --no-reload &
    PHP_PID=$!
}

php_alive() {
    kill -0 "$PHP_PID" 2>/dev/null
}

# Real HTTP GET so the PHP server sees a valid request.
check_php_http() {
    printf "GET /up HTTP/1.0\r\nHost: localhost\r\n\r\n" \
        | nc -w 2 127.0.0.1 $PORT >/dev/null 2>&1
}

start_nginx() {
    nginx -g "daemon off;" &
    NGINX_PID=$!
}

shutdown() {
    kill "$PHP_PID" "$NGINX_PID" 2>/dev/null
    exit 0
}
trap shutdown TERM INT

echo "Starting PHP built-in server on 127.0.0.1:$PORT..." >&2
start_php

# Wait for PHP to answer a real HTTP request before starting nginx.
i=0
while [ $i -lt 50 ]; do
    if check_php_http; then
        break
    fi
    if ! php_alive; then
        echo "PHP died during startup, retrying..." >&2
        start_php
        i=0
        continue
    fi
    sleep 0.2
    i=$((i + 1))
done

if ! check_php_http; then
    echo "PHP never became reachable; restarting container loop." >&2
    kill "$PHP_PID" 2>/dev/null
    exit 1
fi

echo "PHP is up. Starting nginx on :8080..." >&2
start_nginx

# Supervise both processes forever; restart whichever dies.
while true; do
    if ! php_alive; then
        echo "PHP server died — restarting it..." >&2
        start_php
        # Give it a moment to bind; nginx will 502 briefly otherwise.
        j=0
        while [ $j -lt 50 ]; do
            if check_php_http; then break; fi
            sleep 0.2
            j=$((j + 1))
        done
    fi

    if ! kill -0 "$NGINX_PID" 2>/dev/null; then
        echo "nginx died — restarting it..." >&2
        start_nginx
    fi

    sleep 2
done
