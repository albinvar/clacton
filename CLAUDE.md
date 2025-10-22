# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CLACTON is a comprehensive Enterprise Resource Planning (ERP) and Business Management System built with CodeIgniter 3.x. It's a multi-tenant business management platform covering accounting, sales, inventory, CRM, production, and purchase management.

## Technology Stack

- **Backend**: PHP 5.3.7+ with CodeIgniter 3.x MVC framework
- **Database**: MySQL 5.6+ (MySQLi driver)
- **PDF Generation**: DOMPDF 1.1+, FPDF/FPDI
- **Payment Processing**: Stripe PHP SDK
- **Frontend**: Bootstrap, Bootstrap Icons, Font Awesome, jQuery

## Setup and Configuration

### Initial Setup

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Database Setup**:
   - Create MySQL database named `clacton`
   - Import database schema: `mysql -u root -p clacton < db/clacton.sql`
   - Configure database credentials in `ci/application/config/database.php`:
     - Default: `localhost`, user `root`, no password, database `clacton`

3. **Configure Application**:
   - Set base URL in `ci/application/config/config.php` (line 25)
   - Default: `http://localhost/clacton/`
   - Encryption key is set at line 327
   - Timezone configured: Asia/Kolkata (line 52 in MY_Controller.php)

4. **Web Server**:
   - Document root should point to the repository root
   - Front controller: `index.php`
   - Ensure `vendor/autoload.php` exists for Composer dependencies
   - PHP extensions required: mysqli, mbstring, curl, gd

### Running the Application

- **Development**: Access via configured base URL (e.g., `http://localhost/clacton/`)
- **Entry point**: `index.php` (mod_rewrite removes it from URLs)
- **Login module**: `/welcome` (welcome/index)
- **Admin panel**: `/admin`

## Architecture

### Module-Based Structure

The application uses a module-driven architecture with 14 independent business modules under `ci/application/modules/`:

- `accounts/` - Accounting & General Ledger
- `admin/` - User management, permissions
- `business/` - Organization settings, business units, financial years
- `crm/` - Customer Relationship Management
- `documents/` - Document management
- `inventory/` - Stock management, warehouses
- `pos/` - Point of Sale system
- `production/` - Manufacturing & production orders
- `purchase/` - Purchase order management
- `rawmaterial/` - Raw material tracking
- `reports/` - Reporting & analytics
- `sale/` - Sales management
- `welcome/` - Authentication & login

Each module follows MVC structure: `/controllers/`, `/models/`, `/views/`

### Extended Core Classes

Located in `ci/application/core/`:

#### MY_Controller.php

Base controller with critical functionality:

- **Session Management**: Automatically loads user authentication state
- **Multi-Tenancy Context**:
  - `$this->businessid` - Organization identifier
  - `$this->buid` - Business unit identifier
  - `$this->godownid` - Warehouse/location identifier
- **Financial Year Tracking**:
  - `$this->finyearid`, `$this->finstartdate`, `$this->finenddate`, `$this->finname`
- **Currency Settings**:
  - `$this->currency`, `$this->currencysymbol`, `$this->decimalpoints`
- **User Context**:
  - `$this->loggeduserid`, `$this->userrole`, `$this->designation`
  - `$this->permissionmodulearrayspages` - Permission array
- **Rendering Methods**:
  - `render()` - For login/public pages (signindashboard view)
  - `dashboardrender()` - For authenticated user pages (userdashboard view)
  - `framerender()` - For frame-based views
- **Security**: `checksumgen()`, `validchecksumcheck()` for data integrity
- **Utilities**: `time_ago()` for timestamp formatting

**Authentication bypass pages** are whitelisted in lines 182-207 (welcome/index, forgotpassword, etc.)

#### MY_Model.php

Extended model with:

- CRUD operations: `get()`, `insert()`, `update()`, `delete()`
- Soft delete support: Set `protected $soft_delete = TRUE;`
- Relationships: `protected $belongs_to`, `protected $has_many`
- Validation with callbacks
- Lifecycle hooks: `before_create`, `after_create`, `before_update`, etc.
- Query builder chaining

#### MY_Router.php & MY_Loader.php

Custom routing and loading logic to support module-based architecture.

### Multi-Tenancy Implementation

The system supports multiple organizations with business units:

1. **Organization Level** (`businessid`): Top-level business entity
2. **Business Unit Level** (`buid`): Operating units within organization
3. **Warehouse Level** (`godownid`): Physical locations for inventory
4. **Financial Year** (`finyearid`): Accounting period context

All database queries should filter by these identifiers. Session automatically maintains current context (set in MY_Controller constructor).

### Session Variables

Key session data maintained throughout application:

- `authenticationid` - User ID
- `businessid`, `buid`, `godownid` - Tenant context
- `finyearid`, `finstartdate`, `finenddate`, `finname` - Financial year
- `currency`, `currencysymbol`, `presufsymbol`, `decimalpoints` - Display settings
- `usertype` - User role (1 = admin, 2 = regular user)
- `designation` - User designation
- `permissionmodulearrayspages` - Module/page permissions

## Development Guidelines

### Creating New Controllers

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Example extends MY_Controller {
    public function __construct() {
        parent::__construct();
        // Session context is already available:
        // $this->businessid, $this->buid, $this->loggeduserid, etc.

        $this->load->model('modulename/example_model', 'exm');
    }

    public function index() {
        $data = [];
        $data['records'] = $this->exm->get_many_by(['field' => $this->buid]);

        $content = $this->load->view('modulename/example_view', $data, true);
        $this->dashboardrender($content);
    }
}
```

### Creating New Models

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Example_model extends MY_Model {
    protected $table_name = 'table_name';
    protected $primary_key = 'id';
    protected $soft_delete = TRUE;
    protected $soft_delete_key = 'deleted';

    // Define relationships
    protected $belongs_to = ['user'];
    protected $has_many = ['items'];

    // Validation rules
    protected $validate = [
        ['field' => 'name', 'label' => 'Name', 'rules' => 'required']
    ];

    // Custom methods filter by business context
    public function get_by_business_unit($buid) {
        return $this->get_many_by(['buid' => $buid, 'deleted' => 0]);
    }
}
```

### Database Queries

Always filter by tenant context:

```php
// Get records for current business unit
$this->db->where('buid', $this->buid);
$this->db->where('businessid', $this->businessid);
$query = $this->db->get('table_name');
```

### Working with Financial Years

```php
// Access current financial year from controller
$start = $this->finstartdate;
$end = $this->finenddate;

// Query within financial year
$this->db->where('date >=', $this->finstartdate);
$this->db->where('date <=', $this->finenddate);
```

### Permission Checking

```php
// Check if user has permission for current page
$permissions = $this->permissionmodulearrayspages;
// Use this array to control access to features
```

### Routing

Routes are defined in `ci/application/config/routes.php`. The system uses:

- Default route: `welcome/index`
- REST API routes: `/api/example/users/`
- Module routes: `module_name/controller/method`
- Clean URLs without `index.php` (configured in config.php line 37)

## Configuration Files

Key configuration files in `ci/application/config/`:

- `config.php` - Main application config
  - Base URL (line 25)
  - Hooks enabled (line 103)
  - Composer autoload (line 139)
  - Stripe keys (lines 533-534)
  - Session settings (lines 380-386)
- `database.php` - Database credentials
- `autoload.php` - Auto-loaded libraries, helpers, models
- `routes.php` - URL routing rules
- `rest.php` - REST API configuration

## Libraries and Helpers

### Key Libraries (`ci/application/libraries/`)

- `Template.php` - Template rendering engine
- `REST_Controller.php` - Base class for REST APIs
- `S3.php` - AWS S3 integration for file storage
- `Excel.php` - Excel import/export functionality
- `Stripe PHP SDK` - Payment processing
- `FPDF/FPDI` - PDF manipulation
- `TextToSpeech.php` - Text-to-speech conversion

### Key Helpers (`ci/application/helpers/`)

- `application_helper.php` - Application-wide utilities
- `commonfunction_helper.php` - Common functions
- `my_date_helper.php` - Date formatting utilities
- `my_email_helper.php` - Email utilities
- `excel_helper.php` - Excel export helpers
- `export_csv_helper.php` - CSV export helpers
- `dompdf_helper.php` - PDF generation helpers

## File Uploads

Uploaded files are stored in `/uploads/` directory. Ensure proper permissions (writable by web server).

## Security Considerations

- **CSRF Protection**: Currently disabled (line 451 in config.php) - enable for production
- **XSS Filtering**: Currently disabled (line 435) - enable for production
- **Encryption**: Uses custom encryption key (line 327) - change for production
- **Checksums**: Use `checksumgen()` and `validchecksumcheck()` for sensitive data validation
- **Session Expiration**: 7200 seconds (2 hours) - line 382
- **Database**: Uses parameterized queries via Query Builder to prevent SQL injection

## REST API

REST API support via `REST_Controller`:

- API documentation endpoint: `Rest_server.php` controller
- Example routes configured in `routes.php`
- JSON response format
- Authentication should be implemented per API endpoint

## Hooks

Hooks are enabled (config.php line 103). Current hooks in `ci/application/hooks/`:

- `maintenance_hook.php` - Maintenance mode support
- `compress.php` - Output compression

## Debugging

- **Log Level**: Set in `config.php` line 226 (currently 0 = disabled)
  - 1 = Errors only, 2 = Debug, 3 = Info, 4 = All
- **Logs Location**: `ci/application/logs/`
- **Database Debug**: Enabled in development (database.php line 84)
- **Environment**: Set in `index.php` (development/testing/production)

## Common Patterns

### Loading a View with Dashboard Layout

```php
$data = ['key' => 'value'];
$content = $this->load->view('modulename/viewfile', $data, true);
$this->dashboardrender($content);
```

### Getting Current Date/Time

```php
$today = current_date_mysqlformat(); // From application_helper
$timestamp = get_updated_on(); // From application_helper
```

### Checking User Login

```php
// Automatically handled by MY_Controller
// Access logged user: $this->loggeduserid
// Access user role: $this->userrole (1 = admin, 2 = regular)
```

## File Structure Reference

```
clacton/
├── index.php                  # Front controller
├── composer.json              # Composer dependencies
├── db/clacton.sql            # Database schema (7410 lines)
├── uploads/                   # User uploads directory
├── components/                # Frontend assets
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   ├── fonts/                # Web fonts
│   └── images/               # Static images
├── vendor/                    # Composer packages
└── ci/                        # CodeIgniter application
    ├── system/               # CI framework core
    └── application/
        ├── config/           # Configuration files
        ├── controllers/      # Base controllers
        ├── core/             # MY_* extended classes
        ├── modules/          # Business modules (14)
        ├── models/           # Base models
        ├── views/            # View templates
        ├── libraries/        # Custom libraries (30+)
        ├── helpers/          # Helper functions (14+)
        ├── hooks/            # System hooks
        ├── cache/            # Application cache
        ├── logs/             # Log files
        └── third_party/      # Third-party integrations
```
