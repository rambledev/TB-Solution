# TB-Solution landing page — PHP 8.4 + Apache
FROM php:8.4-apache

ENV APP_URL="" \
    APP_DEBUG=0

# Production PHP settings, hide version headers
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'expose_php = Off'; \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /dev/stderr'; \
    } > "$PHP_INI_DIR/conf.d/zz-app.ini" \
    && { \
        echo 'ServerTokens Prod'; \
        echo 'ServerSignature Off'; \
        echo 'ServerName localhost'; \
        echo 'DirectoryIndex index.php'; \
        echo '<DirectoryMatch "^/var/www/html/(config|includes|sections)">'; \
        echo '    Require all denied'; \
        echo '</DirectoryMatch>'; \
        echo '<DirectoryMatch "^/var/www/html/demos/[^/]+/(config|lib|components|pages|data)">'; \
        echo '    Require all denied'; \
        echo '</DirectoryMatch>'; \
        echo '<FilesMatch "^(bootstrap\.php|README\.md)$">'; \
        echo '    Require all denied'; \
        echo '</FilesMatch>'; \
        echo '<FilesMatch "^\.">'; \
        echo '    Require all denied'; \
        echo '</FilesMatch>'; \
        echo '<LocationMatch "^/assets/">'; \
        echo '    Header set Cache-Control "public, max-age=2592000"'; \
        echo '</LocationMatch>'; \
    } > /etc/apache2/conf-available/app.conf \
    && a2enconf app \
    && a2enmod headers

COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/ > /dev/null || exit 1
