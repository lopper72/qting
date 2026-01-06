#!/bin/bash

# Script to start UniApp with API backend

# Create Docker network if it doesn't exist
echo "Creating Docker network 'qting' if it doesn't exist..."
docker network inspect qting >/dev/null 2>&1 || docker network create qting

# Start API backend services
echo "Starting API backend services..."
cd ../API
docker-compose up -d

# Wait for API to be ready
echo "Waiting for API to be ready..."
sleep 15

# Build UniApp (for production)
echo "Building UniApp..."
cd ../uniapp
npm run build:h5

# Start UniApp build service
echo "Starting UniApp build service..."
docker-compose build uniapp-build
docker-compose up -d uniapp-build

echo "Services started successfully!"
echo "UniApp is available at: http://localhost:3001"
echo "API is available at: http://localhost:8000"
