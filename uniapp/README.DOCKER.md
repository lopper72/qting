# Docker Configuration for UniApp

This document explains how to build and run the UniApp application using Docker.

## Overview

We have configured two different approaches for running UniApp with Docker:

1. **Direct Build in Docker** (Production) - Builds the application directly inside Docker container
2. **Volume Mount** (Development) - Uses pre-built files with volume mount for hot-reload

## Option 1: Direct Build in Docker (Production)

This approach builds the application directly inside the Docker container using a multi-stage build process.

### Build and Run

```bash
# Build and start the production container
docker-compose build uniapp-build
docker-compose up -d uniapp-build

# Access the application at http://localhost:3001
```

### How it works

1. **Build Stage**: Uses `node:16-alpine` to install dependencies and build the application
2. **Production Stage**: Copies the built files to `nginx:alpine` for serving
3. The application is built directly in the container without needing pre-built files

### Advantages

- No need to build locally
- Consistent build environment
- Easy deployment

## Option 2: Volume Mount (Development)

This approach uses pre-built files and mounts them as a volume for development.

### Build and Run

```bash
# First, build the application locally
npm run build:h5

# Then start the development container
docker-compose build uniapp-dev
docker-compose up -d uniapp-dev

# Access the application at http://localhost:3000
```

### How it works

1. Build the application locally using `npm run build:h5`
2. The container mounts the `./dist/build/h5` directory
3. Changes to built files are immediately available in the container

### Advantages

- Fast iteration during development
- Hot-reload capability
- Easy to test changes

## Docker Compose Services

- `uniapp-build`: Production service that builds directly in Docker (port 3001)
- `uniapp-dev`: Development service using volume mount (port 3000)

## Files

- `Dockerfile`: Original Dockerfile for development (nginx only)
- `Dockerfile.build`: Multi-stage build Dockerfile for production
- `docker-compose.yml`: Docker Compose configuration with both services

## Notes

- Make sure you have Docker and Docker Compose installed
- The `qting` network must be created before starting containers: `docker network create qting`
- For production, use the `uniapp-build` service
- For development, use the `uniapp-dev` service

## API Configuration

The API endpoint configuration is set up as follows:

- **Development mode** (when running on host machine): `http://localhost:8000/api/v1/`
- **Production mode** (when running in Docker container): `http://qting-api-nginx/api/v1/`

This ensures that:
1. When running UniApp on your local machine (development), it connects to the API at `localhost:8000`
2. When running UniApp in a Docker container (production), it connects to the API container using the Docker service name

## Troubleshooting

If you encounter API connection errors:

1. Make sure the API backend is running: `cd ../API && docker-compose up -d`
2. Check that the `qting` network exists: `docker network ls`
3. Verify both containers are on the same network: `docker network inspect qting`
4. Test API connectivity: `curl http://localhost:8000/api/v1/`

## Starting All Services

You can use the provided script to start all services:

```bash
chmod +x start-services.sh
./start-services.sh
```

This script will:
1. Create the Docker network if it doesn't exist
2. Start the API backend services
3. Build the UniApp application
4. Start the UniApp build service
