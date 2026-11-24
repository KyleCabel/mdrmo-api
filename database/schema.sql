-- TEST DATABASE ONLY.
-- Enable PostGIS in your PostgreSQL provider before running this script.
CREATE EXTENSION IF NOT EXISTS postgis;

CREATE TABLE IF NOT EXISTS incidents (
    id BIGSERIAL PRIMARY KEY,
    emergency_type VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    persons_affected INTEGER NOT NULL DEFAULT 0 CHECK (persons_affected >= 0),
    latitude DOUBLE PRECISION NOT NULL CHECK (latitude BETWEEN -90 AND 90),
    longitude DOUBLE PRECISION NOT NULL CHECK (longitude BETWEEN -180 AND 180),
    gps_accuracy DOUBLE PRECISION NOT NULL CHECK (gps_accuracy >= 0),
    location GEOGRAPHY(POINT, 4326) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS incidents_location_gix
    ON incidents USING GIST (location);
CREATE INDEX IF NOT EXISTS incidents_created_at_idx
    ON incidents (created_at DESC);
