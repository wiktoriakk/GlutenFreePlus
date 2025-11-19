FROM postgres:15

ENV POSTGRES_DB=glutenfree_db
ENV POSTGRES_USER=postgres
ENV POSTGRES_PASSWORD=postgres

# Copy initialization script
COPY docker/db/init.sql /docker-entrypoint-initdb.d/

# Expose PostgreSQL port
EXPOSE 5432