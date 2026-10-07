#!/bin/sh
# Start PHP built-in server and wait until it is listening before starting nginx.
# Restart the PHP server if it exits so a crash no longer produces a persistent 502.
#
# Use a real HTTP request for the health check because nc -z only opens a TCP
# connection with no payload, which the PHP built-in server logs as
# "Malformed HTTP request" and can abort on some versions.
check_php() {
    # Send a minimal HTTP request so the PHP built-in server sees valid HTTP.
    # nc -z alone opens a bare TCP connection with no payload, which the PHP
    # built-in server can log as "Malformed HTTP request" and abort on some
    # versions. Using printf + nc sends a real (if minimal) HTTP request.
    printf "GET / HTTP/1.0\r\nHost: localhost\r\n\r\n" | nc -w 2 127.0.0.1 9000 >/dev/null 2>&1
}
while true; do
    php artisan serve --host=127.0.0.1 --port=9000 --no-reload &
    PHP_PID=$!
    for i in $(seq 1 30); do
        if check_php; then
            break
        fi
        sleep 0.2
    done
    # If the server never came up, keep retrying.
    if ! check_php; then
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
