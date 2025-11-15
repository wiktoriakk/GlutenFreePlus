FROM postgres:15-alpine

# Kopiuj skrypt inicjalizacyjny
COPY docker/db/init.sql /docker-entrypoint-initdb.d/

# Eksponuj port PostgreSQL
EXPOSE 5432