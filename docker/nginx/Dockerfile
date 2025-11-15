FROM nginx:1.25-alpine

# Kopiuj konfigurację nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Ustaw katalog roboczy
WORKDIR /app

# Eksponuj port 80
EXPOSE 80