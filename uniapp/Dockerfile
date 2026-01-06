FROM nginx:alpine

# Create directory for built files
RUN mkdir -p /usr/share/nginx/html

# Copy built UniApp H5 files to nginx (if they exist)
# Note: In production, build files should be copied here
# For development, use volumes in docker-compose

# Copy custom nginx config if needed
# COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
