#!/bin/bash

echo "=== Test Docker Admin Configuration ==="

# Navigate to Admin directory
cd Admin

echo "1. Checking docker-compose.yml syntax..."
docker-compose config > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ docker-compose.yml syntax is valid"
else
    echo "❌ docker-compose.yml has syntax errors"
    docker-compose config
    exit 1
fi

echo "2. Testing network connection..."
# Check if qting network exists
docker network ls | grep -q "qting"
if [ $? -eq 0 ]; then
    echo "✅ Network 'qting' exists"
else
    echo "⚠️  Network 'qting' does not exist. Creating..."
    docker network create qting
fi

echo "3. Testing build process..."
docker-compose build --no-cache
if [ $? -eq 0 ]; then
    echo "✅ Build successful"
else
    echo "❌ Build failed"
    exit 1
fi

echo "4. Testing startup..."
docker-compose up -d
if [ $? -eq 0 ]; then
    echo "✅ Container started successfully"
    echo "5. Checking container status..."
    docker-compose ps
    echo "6. Testing port 8080..."
    curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
    echo ""
    echo "7. Container logs..."
    docker-compose logs --tail=10 admin
else
    echo "❌ Failed to start container"
    exit 1
fi

echo "8. Cleanup test environment..."
docker-compose down

echo "=== Test Complete ==="
echo "Docker Admin configuration is ready for use!"
echo "To start: cd Admin && docker-compose up -d"
echo "To stop: cd Admin && docker-compose down"
