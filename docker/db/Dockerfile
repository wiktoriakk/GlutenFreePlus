FROM postgres:15

ENV POSTGRES_DB=glutenfree_db
ENV POSTGRES_USER=postgres
ENV POSTGRES_PASSWORD=postgres

# Miejsce na init.sql
COPY docker/db/init.sql /docker-entrypoint-initdb.d/ || true