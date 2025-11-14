# CLACTON Documentation

This folder contains technical documentation, implementation guides, and configuration instructions for CLACTON ERP features.

## Documentation Index

### Multi-Currency Feature

1. **[CURRENCY_IMPLEMENTATION_PLAN.md](CURRENCY_IMPLEMENTATION_PLAN.md)**
   - Complete technical implementation guide for multi-currency support
   - Architecture overview and design decisions
   - Step-by-step implementation details
   - Testing checklist and rollback procedures

2. **[CURRENCY_SQL_COMMANDS.md](CURRENCY_SQL_COMMANDS.md)**
   - Database migration SQL commands
   - **MUST RUN** before deploying currency feature
   - Includes verification and rollback scripts
   - Explains what each field does

3. **[PRINT_TEMPLATE_MODIFICATIONS.md](PRINT_TEMPLATE_MODIFICATIONS.md)**
   - Guide for updating invoice print templates
   - Instructions to hide tax columns for foreign currency
   - Currency symbol replacement steps
   - Testing checklist for print outputs

### System Configuration

4. **[PHP_CONFIG_FIX.md](PHP_CONFIG_FIX.md)**
   - Fix for "Undefined offset: 63" error in purchase entry
   - Explains `max_input_vars` PHP limitation
   - Multiple configuration options (php.ini, .htaccess, index.php)
   - Calculation guide for determining required values

## Quick Start Guides

### Implementing Multi-Currency Support

1. **Database Setup:**
   ```bash
   # Open CURRENCY_SQL_COMMANDS.md
   # Run SQL commands in phpMyAdmin
   ```

2. **Deploy Code:**
   ```bash
   git pull origin master
   ```

3. **Test:**
   - Add customer with foreign currency
   - Create bill with automatic exchange rate
   - Verify tax-free billing

### Fixing Purchase Entry Limitation

1. Open `PHP_CONFIG_FIX.md`
2. Choose your configuration method
3. Apply changes
4. Restart web server
5. Test with 100+ products

## Contributing to Documentation

When adding new features or fixes:

1. Create a new `.md` file in this folder
2. Use descriptive filename (e.g., `FEATURE_NAME_GUIDE.md`)
3. Follow existing format with clear sections
4. Include SQL scripts, code examples, and testing steps
5. Update this README with a link to your new document

## Documentation Standards

- Use Markdown format
- Include code blocks with syntax highlighting
- Provide step-by-step instructions
- Add screenshots or examples where helpful
- Include rollback procedures for database changes
- Maintain a "Quick Start" section for each feature

## Related Documentation

- **[Main README](../README.md)** - Project overview and setup
- **[CLAUDE.md](../CLAUDE.md)** - AI assistant instructions and codebase guide
- **[Contributing Guide](../contributing.md)** - Contribution guidelines
