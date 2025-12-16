# Self-Hosted Runner Setup for Test Deployment

This document describes how to set up a self-hosted GitHub Actions runner for test deployments of LehrZeit.

## Prerequisites

- A server or machine to act as the runner (Linux, macOS, or Windows)
- Administrator access to the GitHub repository
- PHP 8.2 or higher installed on the runner machine
- Node.js and NPM installed on the runner machine
- Composer installed on the runner machine
- Web server (Apache/Nginx) configured for Laravel
- MySQL or other database server

## Setting Up the Self-Hosted Runner

### 1. Navigate to GitHub Settings

1. Go to your repository on GitHub: `https://github.com/Sascha007/LehrZeit`
2. Click on **Settings** → **Actions** → **Runners**
3. Click **New self-hosted runner**

### 2. Follow GitHub's Setup Instructions

GitHub will provide OS-specific instructions. For Linux:

```bash
# Create a folder for the runner
mkdir actions-runner && cd actions-runner

# Download the latest runner package
curl -o actions-runner-linux-x64-2.311.0.tar.gz -L https://github.com/actions/runner/releases/download/v2.311.0/actions-runner-linux-x64-2.311.0.tar.gz

# Extract the installer
tar xzf ./actions-runner-linux-x64-2.311.0.tar.gz

# Configure the runner
./config.sh --url https://github.com/Sascha007/LehrZeit --token YOUR_TOKEN

# Install and start the runner as a service
sudo ./svc.sh install
sudo ./svc.sh start
```

### 3. Configure the Runner Environment

Ensure the following are installed on the runner machine:

```bash
# PHP 8.2 or higher
php -v

# Composer
composer --version

# Node.js and NPM
node -v
npm -v

# Database client (MySQL)
mysql --version
```

### 4. Set Up the Web Application Directory

Create a directory where the test deployment will be deployed:

```bash
# Example deployment directory
sudo mkdir -p /var/www/lehrzeit-test
sudo chown -R $USER:www-data /var/www/lehrzeit-test
```

### 5. Configure Web Server

#### For Apache:

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName test.lehrzeit.local
    DocumentRoot /var/www/lehrzeit-test/public

    <Directory /var/www/lehrzeit-test/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lehrzeit-test-error.log
    CustomLog ${APACHE_LOG_DIR}/lehrzeit-test-access.log combined
</VirtualHost>
```

#### For Nginx:

```nginx
server {
    listen 80;
    server_name test.lehrzeit.local;
    root /var/www/lehrzeit-test/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Configure Environment Variables

The runner needs access to certain secrets. In GitHub:

1. Go to **Settings** → **Secrets and variables** → **Actions**
2. Add the following secrets:
   - `DB_HOST`: Database host
   - `DB_DATABASE`: Database name
   - `DB_USERNAME`: Database username
   - `DB_PASSWORD`: Database password
   - Any other environment-specific secrets

### 7. Database Setup

Create a dedicated database for the test deployment:

```sql
CREATE DATABASE lehrzeit_test;
CREATE USER 'lehrzeit_test'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON lehrzeit_test.* TO 'lehrzeit_test'@'localhost';
FLUSH PRIVILEGES;
```

## Using the Test Deployment Workflow

The test deployment workflow can be triggered in two ways:

### 1. Automatic Trigger

Push to the `develop` or `staging` branches:

```bash
git push origin develop
```

### 2. Manual Trigger

1. Go to **Actions** tab in the GitHub repository
2. Select **Test Deployment** workflow
3. Click **Run workflow**
4. Select the branch and click **Run workflow**

## Workflow Steps

The test deployment workflow performs the following:

1. Checks out the code
2. Sets up PHP with required extensions
3. Installs Composer dependencies
4. Installs NPM dependencies
5. Builds frontend assets
6. Sets up environment configuration
7. Runs database migrations
8. Clears and caches Laravel configuration
9. Sets proper file permissions
10. Runs the test suite
11. Notifies on successful deployment

## Troubleshooting

### Runner Not Starting

Check the runner service status:

```bash
sudo ./svc.sh status
```

View logs:

```bash
tail -f _diag/Runner_*.log
```

### Permission Issues

Ensure the runner user has proper permissions:

```bash
sudo chown -R $RUNNER_USER:www-data /var/www/lehrzeit-test
chmod -R 755 /var/www/lehrzeit-test/storage
chmod -R 755 /var/www/lehrzeit-test/bootstrap/cache
```

### Database Connection Issues

Verify database credentials in the `.env` file and ensure the database user has proper permissions.

## Security Considerations

1. **Isolation**: Run the self-hosted runner on an isolated machine or container
2. **Firewall**: Configure firewall rules to restrict access
3. **Updates**: Keep the runner software and dependencies updated
4. **Secrets**: Never commit secrets to the repository; use GitHub Secrets
5. **HTTPS**: Use HTTPS for production deployments

## Monitoring

Monitor the runner:

```bash
# Check if runner is active
sudo ./svc.sh status

# View runner logs
tail -f _diag/Runner_*.log

# Check web server logs
tail -f /var/log/apache2/lehrzeit-test-error.log  # Apache
tail -f /var/log/nginx/error.log                   # Nginx
```

## Maintenance

### Updating the Runner

```bash
cd actions-runner
sudo ./svc.sh stop
# Download and extract new version
./config.sh --url https://github.com/Sascha007/LehrZeit --token YOUR_TOKEN
sudo ./svc.sh start
```

### Removing the Runner

```bash
cd actions-runner
sudo ./svc.sh stop
sudo ./svc.sh uninstall
./config.sh remove --token YOUR_TOKEN
```

## References

- [GitHub Actions Self-hosted Runners Documentation](https://docs.github.com/en/actions/hosting-your-own-runners)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
