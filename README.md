# LehrZeit

GDPR-compliant web app for lecturers to record teaching hours and expenses. Data is managed in monthly billing periods that remain editable until actively submitted. Administration can review, approve, and export finalized data for accounting in a clear, auditable workflow.

## Features

- **Time Tracking**: Record teaching sessions with automatic hour calculation
- **Expense Management**: Track expenses with receipt uploads
- **Monthly Billing Periods**: Organized workflow with four states (OPEN, SUBMITTED, APPROVED, EXPORTED)
- **Role-Based Access**: Admin and Lecturer roles with different permissions
- **GDPR Compliance**: Full audit logging and data export capabilities
- **Data Export**: Export billing periods to CSV and XLSX formats
- **Secure Workflow**: Only editable data when period is OPEN

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL 8.0 or higher (or SQLite for development)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Sascha007/LehrZeit.git
cd LehrZeit
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create a copy of the `.env.example` file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lehrzeit
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

For development, you can use SQLite:
```env
DB_CONNECTION=sqlite
```

7. Run migrations and seeders:
```bash
php artisan migrate --seed
```

This will create the database tables and seed with default users:
- Admin: `admin@lehrzeit.com` / `password`
- Lecturer: `lecturer@lehrzeit.com` / `password`

8. Build frontend assets:
```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

9. Start the development server:
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Usage

### For Lecturers

1. **Create Billing Period**: Create a monthly billing period to start tracking
2. **Add Teaching Sessions**: Record teaching sessions with date, time, subject, and location
3. **Add Expenses**: Track expenses with receipts and categorization
4. **Submit Period**: When ready, submit the billing period for admin approval
5. **Export Data**: Export your data anytime in CSV or XLSX format

### For Administrators

1. **Review Submissions**: View all submitted billing periods
2. **Approve/Reopen**: Approve periods or reopen them for corrections
3. **Export**: Export approved periods for accounting
4. **Monitor**: View statistics and audit logs

## Billing Period States

- **OPEN**: Lecturers can add, edit, or delete data
- **SUBMITTED**: Locked for editing, awaiting admin approval
- **APPROVED**: Approved by admin, ready for export
- **EXPORTED**: Data has been exported for accounting

## GDPR Compliance

- All user actions are logged in audit logs
- Users can export their data at any time
- Data retention policies are enforced
- Privacy policy and terms of service pages included
- Secure file storage for receipts

## Security

- HTTPS recommended for production
- Role-based access control
- File upload validation
- CSRF protection
- SQL injection protection
- XSS protection

## Deployment

The application includes automated deployment workflows and Docker support for easy deployment to test and production environments.

For detailed deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).

### Quick Deployment Options

**Using Docker Compose (recommended for testing):**
```bash
docker-compose up -d
```
Access at `http://localhost:8080`

**Using GitHub Actions:**
The repository automatically builds and publishes Docker images to GitHub Container Registry on every push to `master`.

**Manual deployment:**
See [DEPLOYMENT.md](DEPLOYMENT.md) for comprehensive deployment instructions including Heroku, AWS, DigitalOcean, and manual server deployment.

## Testing

Run the test suite:
```bash
php artisan test
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For issues and questions, please use the GitHub issue tracker.
