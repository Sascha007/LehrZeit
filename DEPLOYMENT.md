# Deployment Guide for LehrZeit Test Page

This guide explains how to deploy the LehrZeit application to various test environments.

## Table of Contents

- [GitHub Actions Automated Deployment](#github-actions-automated-deployment)
- [Docker Deployment](#docker-deployment)
- [Manual Deployment](#manual-deployment)

## GitHub Actions Automated Deployment

The repository includes a GitHub Actions workflow that automatically builds and deploys the application.

### Features

- **Automated Build**: Builds the application on every push to the `master` branch
- **Docker Image**: Creates and publishes a Docker image to GitHub Container Registry
- **Deployment Artifact**: Generates a deployment artifact for manual deployment

### Workflow Triggers

The deployment workflow (`deploy-testpage.yml`) runs:
- On every push to the `master` branch
- On pull requests (build only, no deployment)
- Manually via workflow dispatch

### Accessing the Docker Image

After a successful build, the Docker image is available at:
```
ghcr.io/sascha007/lehrzeit:test
ghcr.io/sascha007/lehrzeit:latest
```

To pull and run the image:
```bash
# Login to GitHub Container Registry
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# Pull the image
docker pull ghcr.io/sascha007/lehrzeit:test

# Run the container
docker run -p 8080:80 ghcr.io/sascha007/lehrzeit:test
```

## Docker Deployment

### Prerequisites

- Docker installed on your system
- Docker Compose (optional, for database)

### Quick Start with Docker Compose

The easiest way to run the application locally for testing:

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

The application will be available at `http://localhost:8080`

### Building the Docker Image Manually

```bash
# Build the image
docker build -t lehrzeit:test .

# Run the container
docker run -p 8080:80 lehrzeit:test
```

### Environment Variables

Configure the application using environment variables:

```bash
docker run -p 8080:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=lehrzeit \
  -e DB_USERNAME=your-username \
  -e DB_PASSWORD=your-password \
  lehrzeit:test
```

## Manual Deployment

### Deployment Artifact

Each successful build creates a deployment artifact (`lehrzeit-app.tar.gz`) that can be downloaded from the GitHub Actions workflow run.

### Steps to Deploy

1. **Download the artifact** from the GitHub Actions workflow run

2. **Extract the artifact**:
   ```bash
   tar -xzf lehrzeit-app.tar.gz -C /var/www/lehrzeit
   cd /var/www/lehrzeit
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   nano .env  # Edit with your configuration
   ```

4. **Set up permissions**:
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate --force
   ```

6. **Configure web server** (Nginx example):
   ```nginx
   server {
       listen 80;
       server_name your-test-domain.com;
       root /var/www/lehrzeit/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

## Deployment to Cloud Platforms

### Deploy to Heroku

1. **Create a Heroku app**:
   ```bash
   heroku create lehrzeit-test
   ```

2. **Add MySQL addon**:
   ```bash
   heroku addons:create jawsdb:kitefin
   ```

3. **Deploy using Docker**:
   ```bash
   heroku container:push web
   heroku container:release web
   ```

### Deploy to DigitalOcean App Platform

1. **Connect your repository** to DigitalOcean App Platform

2. **Use the Dockerfile** for automatic deployment

3. **Add a MySQL database** component

4. **Configure environment variables** in the App Platform dashboard

### Deploy to AWS ECS

1. **Push the Docker image to ECR**:
   ```bash
   aws ecr create-repository --repository-name lehrzeit
   docker tag lehrzeit:test <account-id>.dkr.ecr.<region>.amazonaws.com/lehrzeit:latest
   docker push <account-id>.dkr.ecr.<region>.amazonaws.com/lehrzeit:latest
   ```

2. **Create an ECS task definition** using the pushed image

3. **Configure RDS MySQL** database

4. **Deploy the service** to ECS

## Testing the Deployment

After deployment, verify the application is working:

1. **Access the application** at your deployment URL

2. **Check health endpoint** (if implemented):
   ```bash
   curl http://your-deployment-url/health
   ```

3. **Test login functionality** with test credentials:
   - Admin: `admin@lehrzeit.com` / `password`
   - Lecturer: `lecturer@lehrzeit.com` / `password`

4. **Verify database connectivity** by creating a test billing period

## Troubleshooting

### Common Issues

**Permission Errors**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Database Connection Issues**
- Verify database credentials in `.env`
- Ensure database server is accessible
- Check firewall rules

**Asset Loading Issues**
- Run `npm run build` to rebuild assets
- Check `APP_URL` in `.env` matches your deployment URL
- Verify nginx/Apache configuration serves static files correctly

**Queue Not Processing**
- Ensure supervisor is running
- Check queue worker logs
- Verify database queue table exists

## Security Considerations

For production test deployments:

1. **Use HTTPS** - Configure SSL certificate
2. **Change default credentials** - Update admin and lecturer passwords
3. **Set strong APP_KEY** - Generate new key with `php artisan key:generate`
4. **Configure firewall** - Restrict access to necessary ports only
5. **Regular updates** - Keep dependencies up to date
6. **Environment variables** - Never commit `.env` file to repository

## Support

For deployment issues, please:
1. Check the GitHub Actions workflow logs
2. Review application logs in `storage/logs/laravel.log`
3. Create an issue on GitHub with deployment details
