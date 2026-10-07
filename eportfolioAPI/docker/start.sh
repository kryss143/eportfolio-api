#!/bin/sh
# Start PHP built-in server and wait until it is listening before starting nginx.
# Restart the PHP server if it exits so a crash no longer produces a persistent 502.
while true; do
    php artisan serve --host=127.0.0.1 --port=9000 --no-reload &
    PHP_PID=$!
    for i in $(seq 1 30); do
        if nc -z 127.0.0.1 9000 2>/dev/null; then
            break
        fi
        sleep 0.2
    done
    # If the server never came up, keep retrying.
    if ! nc -z 127.0.0.1 9000 2>/dev/null; then
        echo "PHP built-in server failed to start, retrying..." >&2
        kill $PHP_PID 2>/dev/null
        sleep 1
        continue
    fi
    nginx -g "daemon off;"
    # When nginx stops, kill PHP and restart the whole stack.
    kill $PHP_PID 2>/dev/null
    wait $PHP_PID 2>/dev/null
    sleep 1
done
